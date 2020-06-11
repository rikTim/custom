<?php


namespace Apl\HotelsDbBundle\Service\ConsoleService;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface MultiplyProgressBarInterface
 *
 * @package Apl\HotelsDbBundle\Service\ConsoleService
 */
interface MultiplyProgressBarInterface
{
    /**
     * @param OutputInterface|null $output
     * @return MultiplyProgressBarInterface
     */
    public function withOutput(OutputInterface $output = null): MultiplyProgressBarInterface;

    /**
     * @param int $max
     * @param null|string $format
     * @return null|ProgressBar
     */
    public function newTask(int $max, ?string $format = null): ?ProgressBar;

    /**
     * @param null|ProgressBar $progressBar
     * @param int $step
     * @param null|string $message
     * @param null|string $name
     */
    public function tick(?ProgressBar $progressBar, int $step = 1, ?string $message = null, ?string $name = 'message'): void;

    /**
     * @param null|ProgressBar $progressBar
     * @param null|string $format
     */
    public function endProgress(?ProgressBar $progressBar, ?string $format = null): void;

    public function display(): void;
}