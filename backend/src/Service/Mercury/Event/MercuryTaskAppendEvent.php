<?php

declare(strict_types=1);

namespace App\Service\Mercury\Event;

use App\Model\MercuryTask;
use Symfony\Contracts\EventDispatcher\Event;

class MercuryTaskAppendEvent extends Event
{
    const NAME = 'mercury.task.append';

    protected MercuryTask $task;

    /**
     * @return MercuryTask
     */
    public function getTask(): MercuryTask
    {
        return $this->task;
    }

    /**
     * @param MercuryTask $task
     */
    public function setTask(MercuryTask $task): self
    {
        $this->task = $task;
        return $this;
    }
}
