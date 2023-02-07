<?php

declare(strict_types=1);

namespace Paraunit\Logs\TestHook;

use Paraunit\Logs\ValueObject\Test;
use Paraunit\Logs\ValueObject\TestStatus;
use PHPUnit\Event\Test\Passed as PassedEvent;
use PHPUnit\Event\Test\PassedSubscriber;

class Passed extends AbstractTestHook implements PassedSubscriber
{
    public function notify(PassedEvent $event): void
    {
        $this->write(TestStatus::Passed, Test::fromPHPUnitTest($event->test()), null);
    }
}