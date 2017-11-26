<?php

namespace App\Components\PhpKafkaSdk;


class Process extends \Kafka\Producer\Process
{
    /**
     * start consumer
     *
     * @access public
     * @return void
     */
    public function init()
    {
        // init protocol
        $config = \Kafka\ProducerConfig::getInstance();
        \Kafka\Protocol::init($config->getBrokerVersion(), $this->logger);

        // init process request
        $broker = \Kafka\Broker::getInstance();
        $broker->setProcess(function ($data, $fd) {
            $this->processRequest($data, $fd);
        });

        // init state
//        $this->state = \Kafka\Producer\State::getInstance();
        $this->state = new State();
        if ($this->logger) {
            $this->state->setLogger($this->logger);
        }
        $this->state->setCallback(array(
            \Kafka\Producer\State::REQUEST_METADATA => function () {
                return $this->syncMeta();
            },
            \Kafka\Producer\State::REQUEST_PRODUCE => function () {
                return $this->produce();
            },
        ));
        $this->state->init();

        if (!empty($broker->getTopics())) {
            $this->state->succRun(\Kafka\Producer\State::REQUEST_METADATA);
        }
    }
}