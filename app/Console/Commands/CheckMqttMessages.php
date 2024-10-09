<?php

namespace App\Console\Commands;

use App\Models\Block;
use App\Models\User;
use App\Notifications\MessageNewNotification;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

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

        $mqtt->subscribe('newMessage/#', function ($topic, $payload) {
            $messageData = json_decode($payload, true);

            // Handle the incoming message data
            $this->processMessage($messageData);
        });

        $mqtt->loop(true);

        $mqtt->disconnect();
    }

    protected function processMessage($data)
    {
        // Handle the received data here, e.g., log it, update database, send notifications, etc.
        // Example:
        \Log::info('Received message:', $data);
    }
}
