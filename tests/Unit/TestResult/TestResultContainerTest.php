<?php

declare(strict_types=1);

namespace Tests\Unit\TestResult;

use Paraunit\Configuration\ChunkSize;
use Paraunit\Configuration\PHPUnitConfig;
use Paraunit\Logs\ValueObject\Test;
use Paraunit\TestResult\TestResultContainer;
use Paraunit\TestResult\TestWithAbnormalTermination;
use Tests\BaseUnitTestCase;
use Tests\Stub\StubbedParaunitProcess;

class TestResultContainerTest extends BaseUnitTestCase
{
    public function testAddProcessToFilenames(): void
    {
        $phpUnitConfig = $this->prophesize(PHPUnitConfig::class);
        $testResultFormat = $this->prophesize(TestResultFormat::class);
        $testResultContainer = new TestResultContainer(
            $testResultFormat->reveal(),
            $phpUnitConfig->reveal(),
            $this->mockChunkSize(false)
        );

        $unitTestProcess = new StubbedParaunitProcess('phpunit Unit/ClassTest.php');
        $unitTestProcess->setFilename('ClassTest.php');
        $functionalTestProcess = new StubbedParaunitProcess('phpunit Functional/ClassTest.php');
        $functionalTestProcess->setFilename('ClassTest.php');

        $testResultContainer->addToFilenames($unitTestProcess);
        $testResultContainer->addToFilenames($functionalTestProcess);

        $this->assertCount(2, $testResultContainer->getFileNames());
    }

    public function testHandleLogItemAddsProcessOutputWhenNeeded(): void
    {
        $testResult = new TestWithAbnormalTermination(new Test('function name'));
        $process = new StubbedParaunitProcess();
        $process->setOutput('test output');

        $phpUnitConfig = $this->prophesize(PHPUnitConfig::class);
        $testResultContainer = new TestResultContainer(
            $this->mockTestFormat(),
            $phpUnitConfig->reveal(),
            $this->mockChunkSize(false)
        );
        $testResultContainer->addTestResult($process);

        $this->assertStringContainsString('Possible abnormal termination', $testResult->getFailureMessage());
        $this->assertStringContainsString('test output', $testResult->getFailureMessage());
    }

    public function testHandleLogItemAddsMessageWhenProcessOutputIsEmpty(): void
    {
        $testResult = new TestWithAbnormalTermination(new Test('function name'));
        $process = new StubbedParaunitProcess();
        $process->setOutput('');

        $phpUnitConfig = $this->prophesize(PHPUnitConfig::class);
        $testResultContainer = new TestResultContainer(
            $this->mockTestFormat(),
            $phpUnitConfig->reveal(),
            $this->mockChunkSize(false)
        );
        $testResultContainer->addTestResult($process);

        $this->assertStringContainsString('Possible abnormal termination', $testResult->getFailureMessage());
        $this->assertStringContainsString('<tag><[NO OUTPUT FOUND]></tag>', $testResult->getFailureMessage());
    }

    public function testCountTestResultsCountsOnlyResultsWhichProducesSymbols(): void
    {
        new TestWithAbnormalTermination(new Test('function name'));
        $process = new StubbedParaunitProcess();
        $process->setOutput('');
        $testFormat = $this->prophesize(TestResultFormat::class);
        $testFormat->getTag()
            ->willReturn('tag');

        $phpUnitConfig = $this->prophesize(PHPUnitConfig::class);
        $testResultContainer = new TestResultContainer(
            $testFormat->reveal(),
            $phpUnitConfig->reveal(),
            $this->mockChunkSize(false)
        );
        $testResultContainer->addTestResult($process);

        $this->assertSame(0, $testResultContainer->countTestResults());
    }

    private function mockChunkSize(bool $enabled): ChunkSize
    {
        $chunkSize = $this->prophesize(ChunkSize::class);
        $chunkSize->isChunked()
            ->shouldBeCalled()
            ->willReturn($enabled);

        return $chunkSize->reveal();
    }
}
