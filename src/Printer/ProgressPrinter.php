<?php

declare(strict_types=1);

namespace Paraunit\Printer;

use Paraunit\Lifecycle\AbstractEvent;
use Paraunit\Lifecycle\EngineEnd;
use Paraunit\Printer\ValueObject\OutputStyle;
use Paraunit\TestResult\ValueObject\TestOutcome;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProgressPrinter implements EventSubscriberInterface
{
    final public const MAX_CHAR_LENGTH = 80;

    private const COUNTER_CHAR_LENGTH = 5;

    private int $counter = 0;

    private int $singleRowCounter = 0;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly FinalPrinter $finalPrinter,
    ) {
    }

    /**
     * @return array<class-string<AbstractEvent>, string|array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EngineEnd::class => ['onEngineEnd', 400],
        ];
    }

    public function onEngineEnd(): void
    {
        while (! $this->isRowFull()) {
            $this->output->write(' ');
            ++$this->singleRowCounter;
        }

        $this->printCounter();
    }

    public function printOutcome(TestOutcome $outcome): void
    {
        $this->finalPrinter->increaseOutcomeCount();
        
        if ($this->isRowFull()) {
            $this->printCounter();
        }

        ++$this->counter;
        ++$this->singleRowCounter;

        $style = OutputStyle::fromStatus($outcome)->value;

        $this->output->write(sprintf('<%s>%s</%s>', $style, $outcome->getSymbol(), $style));
    }

    private function printCounter(): void
    {
        $this->output->writeln(sprintf('%6d', $this->counter));
        $this->singleRowCounter = 0;
    }

    private function isRowFull(): bool
    {
        return $this->singleRowCounter === self::MAX_CHAR_LENGTH - (self::COUNTER_CHAR_LENGTH + 1);
    }
}
