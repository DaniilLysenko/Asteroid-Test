<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AppTestCase extends WebTestCase
{
    /** @var Filesystem */
    protected $fs;

    /** @var Client */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->setServerParameters(['HTTP_HOST' => $this->client->getContainer()->getParameter('server_name')]);
        $this->fs = new Filesystem();
        $this->setUpFixtures();
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this::$kernel->getContainer()->getParameter('test_db_path'));
        $this->client = null;
        $this->fs = null;

        parent::tearDown();

        gc_collect_cycles();
    }

    public static function runCommand(
        KernelInterface $kernel,
        array $arguments = []
    ): void {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($arguments);
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    public function assertObjectHasAttributes(
        object $object,
        string $objectName = '',
        array $attributes = []
    ): void {
        foreach ($attributes as $attribute) {
            static::assertObjectHasAttribute($attribute, $object, "Object {$objectName} hasn't attribute '{$attribute}'");
        }

        static::assertCount(\count(get_object_vars($object)), $attributes, "Object {$objectName} attributes count not match");
    }

    protected function setUpFixtures(): void
    {
        if ($this->fs->exists($this->client->getContainer()->getParameter('default_db_path'))) {
            $this->fs->copy(
                $this->client->getContainer()->getParameter('default_db_path'),
                $this->client->getContainer()->getParameter('test_db_path')
            );
        } else {
            static::runCommand($this->client->getKernel(), ['command' => 't:f:l', '-e' => 'test', '-f' => true]);
        }
    }
}
