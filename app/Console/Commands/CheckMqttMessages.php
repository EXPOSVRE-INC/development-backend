<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;


class CheckMqttMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mqtt = MQTT::connection();

        $mqtt->subscribe('message/readMessage/#',  function ($topic, $message){
                $messageData = json_decode($message, true);
                if ($messageData) {
                      Log::info("topic name" , $topic);
                } else {
                    echo "Failed to decode message from topic {$topic}.";
                }
        });

        $mqtt->loop(true);

        $mqtt->disconnect();
    }


}
