<?php


namespace Apl\HotelsDbBundle\Service\ConsoleService;


use Apl\HotelsDbBundle\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;


/**
 * Class AsyncMultiplyProgressBar
 *
 * @package Apl\HotelsDbBundle\Service\ConsoleService
 */
class AsyncMultiplyProgressBar implements MultiplyProgressBarInterface
{
    private static $externalMapping = [];

    private $redrawBuffer = [];

    /**
     * @var string
     */
    private $multiplyProgressBarKey;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * AsyncMultiplyProgressBar constructor.
     *
     * @param string $multiplyProgressBarKey
     */
    public function __construct(string $multiplyProgressBarKey)
    {
        $this->multiplyProgressBarKey = $multiplyProgressBarKey;
    }

    /**
     * @param MultiplyProgressBarInterface $multiplyProgressBar
     * @param string $buffer
     */
    public static function processCommand(MultiplyProgressBarInterface $multiplyProgressBar, string $buffer)
    {
        $command = explode(':', $buffer, 4);
        if (!\is_array($command)) {
            throw new InvalidArgumentException('Incorrect command string');
        }

        if (\count($command) === 4) {
            $command[3] = \json_decode($command[3], true);
        }

        switch ($command[1]) {
            case 'display':
                $multiplyProgressBar->display();
                break;

            case 'newTask':
                if (isset($command[3][1])) {
                    $command[3][1] = '[ASYNC:' . $command[2] . '] ' . $command[3][1];
                }

                self::$externalMapping[$command[2]] = $multiplyProgressBar->newTask(...$command[3]);
                break;

            case 'tick':
                if (isset($command[3][1])) {
                    $command[3][1] = '[ASYNC:' . $command[2] . '] ' . $command[3][1];
                }

                $multiplyProgressBar->tick(self::$externalMapping[$command[2]], ...$command[3]);
                break;

            case 'endProgress':
                if (isset($command[3][0])) {
                    $command[3][0] = '[ASYNC:' . $command[2] . '] ' . $command[3][0];
                }

                $multiplyProgressBar->endProgress(self::$externalMapping[$command[2]], ...$command[3]);
                break;
        }

    }

    /**
     * @param OutputInterface|null $output
     * @return MultiplyProgressBarInterface
     */
    public function withOutput(OutputInterface $output = null): MultiplyProgressBarInterface
    {
        if (!($output instanceof StreamOutput)) {
            return $this;
        }

        $processor = clone $this;
        $processor->output = $output;

        return $processor;
    }

    /**
     * @param int $max
     * @param null|string $format
     * @return null|ProgressBar
     */
    public function newTask(int $max, ?string $format = null): ?ProgressBar
    {
        $progressBar = new ProgressBar(new NullOutput(), $max);
        if ($format) {
            $progressBar->setFormat($format);
        }

        $this->output(__FUNCTION__, $progressBar, \func_get_args());
        $this->display();

        return $progressBar;
    }

    /**
     * @param null|ProgressBar $progressBar
     * @param int $step
     * @param null|string $message
     * @param null|string $name
     */
    public function tick(?ProgressBar $progressBar, int $step = 1, ?string $message = null, ?string $name = 'message'): void
    {
        if (!$progressBar || !$this->output) {
            return;
        }

        $prevStep = $progressBar->getProgress();

        $progressBar->advance($step);
        $this->redrawBuffer[spl_object_hash($progressBar)] = ($this->redrawBuffer[spl_object_hash($progressBar)] ?? 0) + $step;

        $redrawFreq = $progressBar->getMaxSteps() / 20;
        $prevPeriod = (int) ($prevStep / $redrawFreq);
        $currPeriod = (int) ($progressBar->getProgress() / $redrawFreq);

        if ($prevPeriod !== $currPeriod || $progressBar->getProgress() === $progressBar->getMaxSteps()) {
            $this->output(__FUNCTION__, $progressBar, [$this->redrawBuffer[spl_object_hash($progressBar)], $message, $name]);
            $this->display();
            $this->redrawBuffer[spl_object_hash($progressBar)] = 0;
        }
    }

    /**
     * @param null|ProgressBar $progressBar
     * @param null|string $format
     */
    public function endProgress(?ProgressBar $progressBar, ?string $format = null): void
    {
        if (!$progressBar || !$this->output) {
            return;
        }

        $this->output(__FUNCTION__, $progressBar, [$format]);
        $this->display();
    }

    public function display(): void
    {
        $this->output->writeln($this->multiplyProgressBarKey . ':display');
    }

    /**
     * @param string $method
     * @param ProgressBar $progressBar
     * @param array $arguments
     */
    private function output(string $method, ProgressBar $progressBar, array $arguments = []): void
    {
        $this->output->writeln(implode(':', [
            $this->multiplyProgressBarKey,
            $method,
            spl_object_hash($progressBar),
            \json_encode($arguments)
        ]));
    }
}