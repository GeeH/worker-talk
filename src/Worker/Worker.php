<?php

namespace Worker;

use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Predis\Client;

class Worker
{

    /**
     * @var Pheanstalk
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
     * @var \Predis\Client
     */
    private $predis;

    /**
     * Worker constructor.
     * @param Pheanstalk $pheanstalk
     * @param \Predis\Client $predis
     */
    public function __construct(Pheanstalk $pheanstalk, Client $predis)
    {
        $this->pheanstalk = $pheanstalk;
        $this->pheanstalk->watch('jobs');
        $this->predis = $predis;
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
        $this->predis->set($this->id, 'Listening');

        while ($job = $this->pheanstalk->reserve()) {
            $this->predis->set($this->id, 'Found job ' . $job->getId());
            $decodedJob = json_decode($job->getData(), true);

            if (!is_array($decodedJob) || !$this->handleJob($decodedJob, $job)) {
                $this->predis->set($this->id, 'Buried job ' . $job->getId());
                echo 'Buried bad job :( ' . $job->getData() . PHP_EOL;
                $this->pheanstalk->bury($job);
            } else {
                $this->predis->set($this->id, 'Finished job ' . $job->getId());
                $this->pheanstalk->delete($job);
            }

        }
    }

    /**
     * @param array $decodedJob
     * @param \Pheanstalk\Job $job
     * @return bool
     */
    private function handleJob(array $decodedJob, Job $job)
    {
        if ($decodedJob['type'] === 'order') {
            // do whatever you want to handle an order...
            $this->predis->set($this->id, 'Handling order' . $decodedJob['orderId']);
            $result = Order::handle($decodedJob);
            return $result;
        }

        if ($decodedJob['type'] === 'command') {
            $result = $this->handleCommand($decodedJob, $job);
            return $result;
        }
        return false;
    }

    /**
     * @param array $decodedJob
     * @param \Pheanstalk\Job $job
     */
    private function handleCommand(array $decodedJob, Job $job)
    {
        $command = $decodedJob['command'];
        switch ($command) {
            case 'stop':
                $this->predis->set($this->id, 'Handling STOP command');
                echo 'Command "stop" received, stopping...' . PHP_EOL;
                $this->pheanstalk->delete($job);
                exit();
                break;
        }
    }

}
