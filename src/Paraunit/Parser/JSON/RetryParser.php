<?php
declare(strict_types=1);

namespace Paraunit\Parser\JSON;

use Paraunit\Process\ProcessWithResultsInterface;
use Paraunit\Process\RetryAwareInterface;
use Paraunit\TestResult\Interfaces\TestResultBearerInterface;
use Paraunit\TestResult\Interfaces\TestResultHandlerInterface;
use Paraunit\TestResult\MuteTestResult;

/**
 * Class RetryParser
 * @package Paraunit\Parser\JSON
 */
class RetryParser implements ParserChainElementInterface
{
    /** @var TestResultHandlerInterface */
    private $testResultContainer;

    /** @var int */
    private $maxRetryCount;

    /** @var string */
    private $regexPattern;

    /**
     * RetryParser constructor.
     * @param TestResultHandlerInterface $testResultContainer
     * @param int $maxRetryCount
     */
    public function __construct(TestResultHandlerInterface $testResultContainer, int $maxRetryCount = 3)
    {
        $this->testResultContainer = $testResultContainer;
        $this->maxRetryCount = $maxRetryCount;

        $patterns = [
            'The EntityManager is closed',
            // MySQL
            'Deadlock found',
            'Lock wait timeout exceeded',
            // SQLite
            'General error: 5 database is locked',
        ];

        $this->regexPattern = $this->buildRegexPattern($patterns);
    }

    public function handleLogItem(ProcessWithResultsInterface $process, \stdClass $logItem)
    {
        if ($this->isRetriable($process) && $this->isToBeRetried($logItem)) {
            /** @var RetryAwareInterface | TestResultBearerInterface $process */
            $process->markAsToBeRetried();
            $testResult = new MuteTestResult();
            $this->testResultContainer->handleTestResult($process, $testResult);

            return $testResult;
        }

        return null;
    }

    private function isRetriable(TestResultBearerInterface $process): bool
    {
        return $process instanceof RetryAwareInterface && $process->getRetryCount() < $this->maxRetryCount;
    }

    private function isToBeRetried(\stdClass $log): bool
    {
        return property_exists($log, 'status') && $log->status === 'error' && preg_match($this->regexPattern, $log->message);
    }

    /**
     * @param string[] $patterns
     * @return string
     */
    private function buildRegexPattern(array $patterns): string
    {
        $regex = implode('|', $patterns);

        return '/' . $regex . '/';
    }
}