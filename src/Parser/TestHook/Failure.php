<?php

declare(strict_types=1);

namespace Paraunit\Parser\TestHook;

use Paraunit\Parser\ValueObject\TestStatus;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

class Failure extends AbstractTestHook implements FailedSubscriber
{
    public function notify(Failed $event): void
    {
        $this->write(TestStatus::Failed, $event->test(), $event->throwable()->message());
    }
}
