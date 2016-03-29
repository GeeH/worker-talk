<?php

namespace Worker;

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
            if (!is_array($decodedJob) || !$this->handleJob($decodedJob)) {
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
    private function handleJob(array $decodedJob)
    {
        if ($decodedJob['type'] === 'order') {
            // do whatever you want to handle an order...
            $result = Order::handle($decodedJob);
            return $result;
        }
        return false;
    }

}
