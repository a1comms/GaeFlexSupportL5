<?php

namespace A1comms\GaeSupportLaravel\Integration\TaskQueue;

use Exception;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Core\Compute\Metadata;
use Google\Cloud\Tasks\V2\CloudTasksClient;

class Client
{
    private $client;
    private $project;
    private $location;

    private static $myInstance = null;

    public static function instance() {
        if (is_null(self::$myInstance)) {
            self::$myInstance = new Client();
        }

        return self::$myInstance;
    }

    public function __construct() {
        $this->client = new CloudTasksClient();
        $this->project = gae_project();
        $this->location = $this->fetchLocation();
    }

    public function getClient() {
        return $this->client;
    }

    public function getQueueName($queue) {
        return $this->client->queueName($this->project, $this->location, $queue);
    }

    public function getLocation() {
        return $this->location;
    }

    private function fetchLocation() {
        $metadata = new Metadata();
        $zone = $metadata->get('instance/zone');
        //Log::info($zone);
        $zone = explode("/", $zone);
        //Log::info($zone);
        $zone = array_pop($zone);
        //Log::info($zone);

        switch ($zone) {
            case "eu2":
            case "eu5":
            case "eu6":
                return "europe-west1";
            case "us6":
                return "us-central1";
            case "us14":
                return "us-central1";
            default:
                throw new Exception("Unknown App Engine Region Code: " . $zone);
        }
    }
}
