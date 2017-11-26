<?php

namespace App\Components\Kafka;

use App;
use Carbon\Carbon;
use Log;
use Cache;
use App\Components\PhpKafkaSdk\Producer;
use RdKafka\Conf;
use RdKafka\TopicConf;
use RdKafka\KafkaConsumer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * 发送数据到kafka服务器
 * (help document): https://arnaud-lb.github.io/php-rdkafka/phpdoc/class.rdkafka-producer.html.
 */
class KafkaClient
{
    /**
     * 测试环境已经搭建好kafka集群，已经测试完毕：.
     */
    public static function send($topic, $message, $partionId, $retryCount = 0)
    {
        if (in_array(env('APP_ENV'), ['testing', 'development', 'staging', 'production'])) {
            $profiles = [];
            $profiles['time']['start'] = microtime(true);
            $profiles['memory']['start'] = memory_get_usage();
        }

//        $rk = new Producer();
//        if (App::environment('development', 'testing')) {
//            $host = '172.16.100.100:9092,172.16.100.101:9092,172.16.100.102:9092';
//            $rk->setLogLevel(LOG_DEBUG);
//            $rk->addBrokers($host);
//        } else {
//            //正式环境
//            $host = '10.10.104.4, 10.10.103.230, 10.10.112.18';
//            $rk->addBrokers($host);
//        }
//
//        try {
//            $tp = $rk->newTopic($topic);
//            $tp->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($message));
////            $tp->produce($partionId, 0, json_encode($message));
//
//            Log::info(__FILE__.'('.__LINE__.'), send data to kafka success,', [
//                'topic' => $topic,
//                'message' => $message,
//                'host' => $host,
//                'partionId' => $partionId,
//            ]);
//        } catch (\Exception $e) {
//            Log::error(__FILE__.'('.__FILE__.'), send data to kafka fail', [
//                'code' => $e->getCode(),
//                'msg' => $e->getMessage(),
//            ]);
//        }

        Log::info(__FILE__ . '(' . __LINE__ . '), kafka send start, ', [
            'topic' => $topic,
            'message' => $message,
            'retry_count' => $retryCount,
        ]);

        $host = config('services.kafka.host');
        $version = config('services.kafka.version');

        //是否需要重发
        $needRetry = false;
        try {
            $logger = new Logger('my_logger');
            $logger->pushHandler(new StreamHandler(App\Components\Config\EnvConfig::env('kafka_LOG_PATH'), Logger::DEBUG));

            $config = \Kafka\ProducerConfig::getInstance();
            $config->setMetadataRefreshIntervalMs(10000);
            $config->setMetadataBrokerList($host);
            $config->setBrokerVersion($version);
            $config->setRequiredAck(1);
            $config->setIsAsyn(false);
            $config->setProduceInterval(500);
            $key = '';
            $producer = new Producer(function() use ($topic, $message, $key) {
                return array(
                    array(
                        'topic' => $topic,
                        'value' => json_encode($message),
                        'key' => $key,
                    ),
                );
            }, $logger);
            $producer->success(function($result) use ($topic, $message, $retryCount) {
                Log::info(__FILE__ . '(' . __LINE__ . '), kafka send success, ', [
                    'topic' => $topic,
                    'message' => $message,
                    'result' => $result,
                    'retry_count' => $retryCount,
                ]);
            });
            $producer->error(function($errorCode) use ($topic, $message, $retryCount, &$needRetry) {
                Log::error(__FILE__ . '(' . __LINE__ . '), kafka send fail, ', [
                    'topic' => $topic,
                    'message' => $message,
                    'errorCode' => $errorCode,
                    'retry_count' => $retryCount,
                ]);
                $needRetry = true;
            });
            $producer->send();
        } catch (\Exception $e) {
            Log::info(__FILE__  . '(' . __LINE__  . '), kafka send message exception, ', [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'topic' => $topic,
                'message' => $message,
                'retry_count' => $retryCount,
            ]);
            $needRetry = true;
        }

        if (in_array(env('APP_ENV'), ['testing', 'development', 'staging', 'production'])) {
            $profiles['time']['end'] = microtime(true);
            $profiles['time']['cost'] = $profiles['time']['end'] - $profiles['time']['start'];
            $profiles['memory']['end'] = memory_get_usage();
            Log::info(
                __FILE__ . '(' . __LINE__ . ') ' . 'start: ' . $profiles['time']['start'] . ', end: ' . $profiles['time']['end']
                . ", Kafka send message time cost: {$profiles['time']['cost']} s"
                . ", topic:" . $topic
                . ", message:" . json_encode($message)
            );
        }

        //失败重试发送3次(计数从0开始累计)
        if ($needRetry && ($retryCount < 2)) {
            self::send($topic, $message, $partionId, ++$retryCount);
        }
    }

    //http://wiki.corp.ttyongche.com:8360/confluence/pages/viewpage.action?pageId=13566631
    //发送用户行为  开关锁时的坐标
    public static function sendUserAction(array $data)
    {
        $topic = 'ebike_user_event';//线上和测试环境
        if (in_array(env('APP_ENV'), ['development'])) {
            $topic = 'ebike_user_event_dev';
        } else if (in_array(env('APP_ENV'), ['staging'])) {
            $topic = 'ebike_user_event_slave';
        }

        if (isset($data['sn'])) {
            $dt['sn'] = $data['sn'];
        }

        if (isset($data['bike_sn'])) {
            $dt['bike_sn'] = $data['bike_sn'];
        }

        if (isset($data['cfg_id'])) {
            $dt['cfg_id'] = $data['cfg_id'];
        }

        $message = [
            'action' => $data['action'],
        ];
        if (isset($data['user_id'])) {
            $message['user_id'] = $data['user_id'];
        }

        if (isset($dt)) {
            $message = array_merge($message, ['data' => $dt]);
        }

        static::send($topic, $message, 1);
    }

    //发送用户坐标
    public static function sendUserGps(array $data)
    {
        $topic = 'ebike_user_geo_event';
        if (in_array(env('APP_ENV'), ['development'])) {
            $topic = 'ebike_user_geo_event_dev';
        } else if (in_array(env('APP_ENV'), ['staging'])) {
            $topic = 'ebike_user_geo_event_slave';
        }

        if (empty($data['gps']) || !isset($data['gps']['latitude'])) {
            Log::info(__FILE__ . '(' . __LINE__ . '), [kafka] gps is null, ', [
                'data' => $data
            ]);
            return;
        }

        $message = [
            'order_sn' => $data['order_sn'],
            'gps' => $data['gps'],
            'ts' => Carbon::now()->timestamp,
            'type' => $data['type'],
        ];

        static::send($topic, $message, 1);
    }

    //发送埋点数据
    public static function sendBuriedPoint(array $data)
    {
        $topic = env('KAFKA_BURIED_TOPIC');
        if (in_array(env('APP_ENV'), ['development'])) {
            $topic = $topic.'_dev';
        } else if (in_array(env('APP_ENV'), ['staging'])) {
            $topic = $topic.'_slave';
        }

        if (empty($data['name'])) {
            Log::info(__FILE__ . '(' . __LINE__ . '), [kafka] message name is null, ', [
                'data' => $data
            ]);
            return;
        }

        $message = $data;

        static::send($topic, $message, 1);
    }

    public static function sendOrderInfo(array $data)
    {
        $topic = 'ebike_user_event';
        if (in_array(env('APP_ENV'), ['development'])) {
            $topic = 'ebike_user_event_dev';
        } else if (in_array(env('APP_ENV'), ['staging'])) {
            $topic = 'ebike_user_event_slave';
        }

        $message = [
            'action' => $data['action'],
            'user_id' => $data['user_id'],
            'data' => [
                'sn' => $data['sn'],              //订单编号
                'bike_sn' => $data['bike_sn'],    //车辆编号
            ],
        ];

        static::send($topic, $message, 1);
    }

    public static function consume($memory = 100)
    {
        $conf = new Conf();

        // Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    Log::info('Assign: ', ['partitions' => $partitions]);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    Log::info('Revoke: ', ['partitions' => $partitions]);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', 'ebike_order_1');

        // Initial list of Kafka brokers
        $host = config('services.kafka.host');
        $conf->set('metadata.broker.list', $host);
        $topicConf = new TopicConf();

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        // Action to take when there is no initial offset in offset store or the desired
        // offset is out of range: 'smallest','earliest' - automatically reset the offset to the smallest offset,
        // 'largest','latest' - automatically reset the offset to the largest offset,
        // 'error' - trigger an error which is retrieved by consuming messages and checking 'message->err'.
        $topicConf->set('auto.offset.reset', 'largest');
        //$topicConf->set('auto.offset.reset', 'smallest');

        // Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);
        $consumer = new KafkaConsumer($conf);

        // Subscribe to topic 'ebike_order_event'
        $topic = 'ebike_order_event';
        if (App::environment('development')) {
            $topic = 'ebike_order_event_dev';
        } else if (App::environment('staging')) {
            $topic = 'ebike_order_event_slave';
        }
        $consumer->subscribe([$topic]);
        Log::info('[kafka] host: ' . $host . ', topic: ' . $topic);
        Log::info('[kafka] Waiting for partition assignment... (make take some time when');
        Log::info('[kafka] quickly re-joining the group after leaving it.)');

        $lastRestartTime = time();
        $key = 'kafka:client:restart';
        Cache::forever($key, $lastRestartTime);
        while (true) {
            $message = $consumer->consume(10*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    Log::info('[kafka] message content = ' . json_encode($message));
                    //TODO:处理业务
                    event(new App\Events\Notice\OrderCalEvent(['payload' => $message->payload]));
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    Log::info('[kafka] No more messages; will wait for more');
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    Log::info('[kafka] Timed out');
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }

            //检查一下是否应该重启
            $isMemory = static::memoryExceeded($memory);
            $isRestart = static::kafkaShouldRestart($key, $lastRestartTime);
            if ($isMemory || $isRestart) {
                Log::info('[kafka] the consume of kafka exit, memory: ' . $isMemory . ', restart: ' . $isRestart);
                break;
            }
        }
    }

    private static function memoryExceeded($memoryLimit)
    {
        return (((memory_get_usage() / 1024) / 1024) >= $memoryLimit);
    }

    private static function kafkaShouldRestart($key, $lastRestartTime)
    {
        $currentTime = Cache::get($key);
        if (empty($currentTime)) {
            return 0;
        }
        return $currentTime != $lastRestartTime;
    }
}
