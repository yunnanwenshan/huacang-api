<?php

namespace App\Components;

use Illuminate\Foundation\Bootstrap\ConfigureLogging;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Writer;

class CustomConfigureLogging extends ConfigureLogging
{
    protected function configureSingleHandler(Application $app, Writer $log)
    {
        $log->useFiles(env('LOG_FILE'));
    }

    protected function configureDailyHandler(Application $app, Writer $log)
    {
        $log->useDailyFiles(
            env('LOG_FILE'),
            $app->make('config')->get('app.log_max_files', 5)
        );
    }
}