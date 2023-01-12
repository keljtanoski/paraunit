<?php

declare(strict_types=1);

namespace Paraunit\Parser\TestHook;

use Paraunit\Parser\ValueObject\TestStatus;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber;

class Warning extends AbstractTestHook implements WarningTriggeredSubscriber
{
    public function notify(WarningTriggered $event): void
    {
        $this->write(TestStatus::WarningTriggered, $event->test(), $event->message());
    }
}
