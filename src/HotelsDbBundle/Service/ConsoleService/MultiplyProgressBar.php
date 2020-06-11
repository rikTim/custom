<?php


namespace Apl\HotelsDbBundle\Service\ConsoleService;


use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Monolog\Logger;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

/**
 * Class MultiplyProgressBar
 *
 * @package Apl\HotelsDbBundle\Service
 */
class MultiplyProgressBar implements MultiplyProgressBarInterface
{
    use LoggerAwareTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array[]
     */
    private $progressBars;

    /**
     * @var int
     */
    private $currentLevel = 0;

    /**
     * @param OutputInterface|null $output
     * @return MultiplyProgressBar|MultiplyProgressBarInterface
     */
    public function withOutput(OutputInterface $output = null): MultiplyProgressBarInterface
    {
        if (!($output instanceof StreamOutput)) {
            return $this;
        }

        $processor = clone $this;
        $processor->progressBars = [];
        $processor->output = $output;
        return $processor;
    }

    /**
     * @param int $max
     * @param null|string $format
     * @return ProgressBar
     */
    public function newTask(int $max, ?string $format = null): ?ProgressBar
    {
        if (!$this->output) {
            return null;
        }

        $progressBar = new ProgressBar($this->output, $max);
        $this->progressBars[spl_object_hash($progressBar)] = [
            'level' => \count($this->progressBars) + 1,
            'progressBar' => $progressBar,
        ];

        if ($format) {
            $progressBar->setFormat($format);
        }

        $this->display();

        return $progressBar;
    }

    /**
     * @param ProgressBar $progressBar
     * @param int $step
     * @param null|string $message
     * @param null|string $name
     */
    public function tick(?ProgressBar $progressBar, int $step = 1, ?string $message = null, ?string $name = 'message'): void
    {
        if (!$progressBar || !$this->output) {
            return;
        }

        $key = spl_object_hash($progressBar);
        if (!isset($this->progressBars[$key])) {
            throw new RuntimeException('Cant find progress level');
        }

        $this->goToLevel($this->progressBars[$key]['level']);

        if ($message) {
            $progressBar->setMessage($message, $name);
        }

        if ($step) {
            $progressBar->advance($step);
        }

        $this->display();
    }

    /**
     * @param ProgressBar $progressBar
     */
    public function endProgress(?ProgressBar $progressBar, ?string $format = null): void
    {
        if (!$progressBar || !$this->output) {
            return;
        }

        $key = spl_object_hash($progressBar);
        if (!isset($this->progressBars[$key])) {
            throw new RuntimeException('Cant find progress level');
        }

        $this->goToLevel($this->progressBars[$key]['level']);
        $progressBar->clear();

        if ($format) {
            $this->goToLevel(0);
            $progressBar->setFormat($format);
            $progressBar->display();
            $this->output->write("\n");
        }

        foreach ($this->progressBars as $nextProgressBar) {
            if ($nextProgressBar['level'] > $this->progressBars[$key]['level']) {
                $nextProgressBar['level']--;
            }
        }

        unset($this->progressBars[$key]);
        $this->display();
    }

    public function display(): void
    {
        $this->goToLevel(0);
        foreach ($this->progressBars as $nextProgressBar) {
            $this->goToLevel($nextProgressBar['level']);
            $nextProgressBar['progressBar']->display();
        }
        $this->goToLevel(0);

        // Clear row
        $this->output->write("\x0D\e[2K");
    }

    /**
     * @param int $level
     */
    private function goToLevel(int $level): void
    {
        // Hide cursor
        $this->output->write("\e[?25l");
        if ($this->currentLevel !== $level) {
            if ($this->currentLevel < $level) {
                $this->output->write(str_repeat("\n", $level - $this->currentLevel));
            } else {
                $this->output->write(str_repeat("\033[1A", $this->currentLevel - $level));
            }

            $this->currentLevel = $level;
        }

        // Show cursor
        $this->output->write("\e[?25h");
    }
}