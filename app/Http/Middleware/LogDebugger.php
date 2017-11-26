<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Http\JsonResponse;
use Log;
use DB;

class LogDebugger
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $profiles = [];
        $profiles['time']['start'] = microtime(true);
        $profiles['memory']['start'] = memory_get_usage();

        if (App::environment('development', 'testing')) {
            DB::listen(function ($sql, $bindings, $time) {
                Log::debug($sql);
                Log::debug($bindings);
                Log::debug($time);
            });
        }

        $response = $next($request);

        if (App::environment('development', 'testing')) {
            if ($response instanceof JsonResponse) {
                $text = json_encode($response->getData(true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            } else {
                $text = $response->getContent();

                $response_array = json_decode($text, true);

                if (JSON_ERROR_NONE === json_last_error()) {
                    $text = json_encode($response_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }
            }

            $profiles['time']['end'] = microtime(true);
            $profiles['time']['cost'] = $profiles['time']['end'] - $profiles['time']['start'];

            $profiles['memory']['end'] = memory_get_usage();

            function convert($size)
            {
                $unit = ['b','kb','mb','gb','tb','pb'];

                return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
            }

            $profiles['memory']['max'] = convert(memory_get_peak_usage());

            function cURL($request)
            {
                $args = [];
                $uri = $request->fullUrl() ? $request->fullUrl() : $request->url();
                if (($request->secure() || $request->header('X-Forwarded-Proto') == 'https') && strripos($uri, 'http://', 0) === 0) {
                    $uri = substr_replace($uri, 'https://', 0, 7);
                }
                $args[] = escapeshellarg($uri);

                foreach ($request->header() as $name => $lines) {
                    if (in_array(strtolower($name), ['x-forwarded-host', 'x-forwarded-for', 'x-forwarded-port', 'x-forwarded-proto'])) {
                        continue;
                    }

                    foreach ($lines as $line) {
                        if (empty($line)) {
                            continue;
                        }

                        $args[] = '-H '.escapeshellarg($name.': '.$line);
                    }
                }
                if ($request->isMethod('post')) {
                    $args[] = '-d '.escapeshellarg($request->getContent());
                }
                $cmd = 'curl -i '.implode(' ', $args);

                return $cmd;
            }

            Log::debug(
                __FILE__.'('.__LINE__.') '.PHP_EOL
                ."\t============================== 1. Request   ==============================".PHP_EOL
                ."\t".str_ireplace("\n", "\n\t", $request->__toString()).PHP_EOL
                ."\t============================== 2. Response  ==============================".PHP_EOL
                ."\t".str_ireplace("\n", "\n\t", $text).PHP_EOL
                ."\t============================== 3. cURL      ==============================".PHP_EOL
                ."\t".cURL($request).PHP_EOL
            );
        }

        return $response;
    }
}
