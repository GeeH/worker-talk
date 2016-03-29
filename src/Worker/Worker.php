<?php

namespace Worker;

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class Worker
{

    /**
     * @var \Pheanstalk\Pheanstalk
     */
    private $pheanstalk;
    /**
     * @var string
     */
    private $id;
    /**
     * @var int
     */
    private $memoryLimit = 100000;

    /**
     * Worker constructor.
     * @param \Pheanstalk\Pheanstalk $pheanstalk
     */
    public function __construct(Pheanstalk $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
        $this->pheanstalk->watch('jobs');
    }

    /**
     * Start Worker
     * @param null $id
     */
    public function start($id = null)
    {
        if (!is_string($id) || strlen($id) < 5) {
            $id = $this->generateId();
        }

        $this->id = $id;
        $this->pheanstalk->watch($this->id);
        $this->listen();
    }

    /**
     * Generate a Unique Worker Id
     * @return mixed
     */
    private function generateId()
    {
        return uniqid();
    }

    /**
     * Listen for jobs
     */
    private function listen()
    {
        echo 'Worker ' . $this->id . ' starting...' . PHP_EOL;
        while ($job = $this->pheanstalk->reserve()) {
            $decodedJob = json_decode($job->getData(), true);
            if (!is_array($decodedJob) || !$this->handleJob($decodedJob, $job)) {
                echo 'Buried bad job :(' . PHP_EOL;
                $this->pheanstalk->bury($job);
            } else {
                $this->pheanstalk->delete($job);
            }
        }
    }

    /**
     * @param array $decodedJob
     * @return bool
     */
    private function handleJob(array $decodedJob, Job $job)
    {
        if ($decodedJob['type'] === 'order') {
            // do whatever you want to handle an order...
            $result = Order::handle($decodedJob);
            return $result;
        }

        if ($decodedJob['type'] === 'command') {
            $result = $this->handleCommand($decodedJob, $job);
            return $result;
        }
        return false;
    }

    private function handleCommand(array $decodedJob, Job $job)
    {
        $command = $decodedJob['command'];
        switch ($command) {
            case 'stop':
                echo 'Command "stop" received, stopping...' . PHP_EOL;
                $this->pheanstalk->delete($job);
                exit();
                break;
        }
    }

}
