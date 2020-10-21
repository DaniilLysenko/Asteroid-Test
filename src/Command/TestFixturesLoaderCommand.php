<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class TestFixturesLoaderCommand extends Command
{
    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    private $defaultPath;

    /** @var string */
    private $testDbPath;

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function getTestDbPath(): string
    {
        return $this->testDbPath;
    }

    public function setTestDbPath(string $testDbPath): void
    {
        $this->testDbPath = $testDbPath;
    }

    public function getDefaultPath(): string
    {
        return $this->defaultPath;
    }

    public function setDefaultPath(string $defaultPath): void
    {
        $this->defaultPath = $defaultPath;
    }

    public static function runCommand(KernelInterface $kernel, array $arguments = []): BufferedOutput
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($arguments);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }

    protected function configure(): void
    {
        $this
            ->setName('test:fixtures:load')
            ->setDescription('Create DB with data for tests.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force upgrade fixtures')
        ;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $fs = new Filesystem();

        if ($input->getOption('force') || !$fs->exists($this->getDefaultPath())) {
            $commands = [
                ['command' => 'd:s:d', '--force' => true, '-e' => 'test'],
                ['command' => 'd:s:u', '--force' => true, '-e' => 'test'],
                ['command' => 'h:f:l', '--no-interaction' => true, '--no-bundles' => true, '-e' => 'test'],
            ];

            foreach ($commands as $command) {
                $out = static::runCommand($this->getKernel(), $command);
                $output->writeln($out->fetch());
            }

            if ($fs->exists($this->getTestDbPath())) {
                $fs->copy($this->getTestDbPath(), $this->getDefaultPath());
            }
        }

        $output->writeln('Test fixtures was loaded successfully.');

        return 0;
    }
}
