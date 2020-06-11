<?php


namespace Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData;
use Apl\HotelsDbBundle\Exception\LogicException;
use Apl\HotelsDbBundle\Exception\RuntimeException;
use Apl\HotelsDbBundle\Service\ConsoleService\MultiplyProgressBar;
use Apl\HotelsDbBundle\Service\LoggerAwareTrait;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class ImportScenario
 * @package Apl\HotelsDbBundle\Service\ServiceProvider\ImportStaticData
 */
class ImportScenario
{
    use LoggerAwareTrait;

    /**
     * @var callable[]
     */
    private $steps;

    /**
     * @var MultiplyProgressBar
     */
    private $multiplyProgressBar;

    /**
     * ImportScenario constructor.
     */
    public function __construct()
    {
        $this->steps = [];
    }

    /**
     * @param MultiplyProgressBar $multiplyProgressBar
     * @return ImportScenario
     */
    public function setMultiplyProgressBar(MultiplyProgressBar $multiplyProgressBar): ImportScenario
    {
        $this->multiplyProgressBar = $multiplyProgressBar;
        return $this;
    }

    /**
     * @param string $stepName
     * @param callable $callback
     * @return ImportScenario
     * @throws \Apl\HotelsDbBundle\Exception\LogicException
     */
    public function pushStep(string $stepName, callable $callback): ImportScenario
    {
        if (!\is_callable($callback)) {
            throw new LogicException(sprintf('Missing callback for import step "%s"', $stepName));
        }

        $this->steps[$this->normalizeStepName($stepName)] = $callback;
        return $this;
    }

    /**
     * @param string $stepName
     * @param mixed ...$args
     */
    public function runFromStep(string $stepName, ...$args): void
    {
        $stepName = $this->normalizeStepName($stepName);
        if (!isset($this->steps[$stepName])) {
            throw new RuntimeException(sprintf('Import step "%s" not exists', $stepName));
        }

        $steps = [];
        $isFind = false;
        foreach ($this->steps as $stepKey => $callback) {
            if (!$isFind && $stepKey === $stepName) {
                $isFind = true;
            }

            if ($isFind) {
                $steps[] = $stepKey;
            }
        }

        $this->runSteps($steps, ...$args);
    }

    /**
     * @param null|string|array $steps
     * @param array $args
     * @throws \Apl\HotelsDbBundle\Exception\RuntimeException
     */
    public function runSteps($steps, ...$args): void
    {
        if (!$steps) {
            $preparedSteps = $this->steps;
        } else {
            $preparedSteps = [];

            if (!\is_array($steps)) {
                $steps = \explode(',', $steps);
            }

            foreach ($steps as $stepName) {
                $stepName = $this->normalizeStepName($stepName);
                if (!isset($this->steps[$stepName])) {
                    throw new RuntimeException(sprintf('Import step "%s" not exists', $stepName));
                }

                $preparedSteps[$stepName] = $this->steps[$stepName];
            }

        }

        if ($this->multiplyProgressBar) {
            $progressBar = $this->multiplyProgressBar->newTask(
                \count($preparedSteps),
                '%current%/%max% [%bar%] %percent:3s%% %elapsed:6s% %memory:6s% | Step: %message%'
            );
        }

        foreach ($preparedSteps as $stepName => $callback) {
            $this->logger->info('Start step', ['stepName' => $stepName]);
            if ($this->multiplyProgressBar && isset($progressBar)) {
                $this->multiplyProgressBar->tick($progressBar, 0, $stepName);
            }

            $callback(...$args);

            if ($this->multiplyProgressBar && isset($progressBar)) {
                $this->multiplyProgressBar->tick($progressBar);
            }

            $this->logger->info('End step', ['stepName' => $stepName]);
            gc_collect_cycles();
        }

        if ($this->multiplyProgressBar && isset($progressBar)) {
            $this->multiplyProgressBar->endProgress($progressBar, '[%elapsed:6s% %memory:6s%] Scenario complete');
        }
    }

    /**
     * @param string $stepName
     * @return string
     */
    private function normalizeStepName(string $stepName): string
    {
        return \strtolower(\trim($stepName));
    }
}