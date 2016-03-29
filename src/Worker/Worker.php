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

    public function __construct(Pheanstalk $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
        $this->pheanstalk->watch('jobs');
        $this->pheanstalk->watch('commands');
    }

    public function start($id = null)
    {
        if (!is_string($id) || strlen($id) < 5) {
            $id = $this->generateId();
        }

        $this->id = $id;
        $this->listen();
    }

    private function generateId()
    {
        return uniqid();
    }

    private function listen()
    {
        echo 'Worker ' . $this->id . ' starting...' . PHP_EOL;
        while ($job = $this->pheanstalk->reserve()) {
            echo($job->getData());
            echo PHP_EOL;
            $this->pheanstalk->delete($job);
        }
    }

}
