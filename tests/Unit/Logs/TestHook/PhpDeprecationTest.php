<?php

declare(strict_types=1);

namespace Tests\Unit\Logs\TestHook;

use Paraunit\Logs\TestHook\PhpDeprecation;
use Paraunit\Logs\ValueObject\LogStatus;
use PHPUnit\Event\Test\PhpDeprecationTriggered;

/**
 * @template-extends AbstractTestHookTestCase<PhpDeprecation, PhpDeprecationTriggered>
 */
class PhpDeprecationTest extends AbstractTestHookTestCase
{
    protected function createSubscriber(): PhpDeprecation
    {
        return new PhpDeprecation();
    }

    protected function getExpectedStatus(): LogStatus
    {
        return LogStatus::Deprecation;
    }

    protected function createEvent(): PhpDeprecationTriggered
    {
        return new PhpDeprecationTriggered(
            $this->createTelemetryInfo(),
            $this->createPHPUnitTest(),
            $this->getExpectedMessage(),
            'testFile.php',
            123
        );
    }

    protected function getExpectedMessage(): string
    {
        return 'test message';
    }
}