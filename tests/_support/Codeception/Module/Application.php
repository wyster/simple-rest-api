<?php declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use DI\Container;
use Codeception\Lib\Framework;
use Codeception\Lib\Connector\Application as ApplicationConnector;
use Zend\Db\Adapter\AdapterInterface;

class Application extends Framework
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Bootstrap file path
     */
    private $bootstrapFile;

    protected $config = [
        'bootstrap' => 'config/bootstrap.php',
        'cleanup' => true,
    ];

    public function _initialize(): void
    {
        $this->bootstrapFile = Configuration::projectDir() . $this->config['bootstrap'];
        if (!file_exists($this->bootstrapFile)) {
            throw new ModuleConfigException(
                __CLASS__,
                'Bootstrap file does not exist in ' . $this->config['bootstrap'] . "\n"
            );
        }

        $this->client = new ApplicationConnector();
    }

    /**
     * HOOK: before scenario
     *
     * @param TestInterface $test
     * @throws ModuleException
     */
    public function _before(TestInterface $test)
    {
        /** @noinspection PhpIncludeInspection */
        $application = require $this->bootstrapFile;
        $this->container = $application;

        $this->client->setContainer($this->container);

        if ($this->config['cleanup'] && $this->container->has(AdapterInterface::class)) {
            $this->container->get(AdapterInterface::class)->getDriver()->getConnection()->beginTransaction();
            $this->debugSection('Database', 'Transaction started');
        }
    }

    /**
     * HOOK: after scenario
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        if ($this->config['cleanup'] && $this->container->has(AdapterInterface::class)) {
            $db = $this->container->get(AdapterInterface::class);
            $db->getDriver()->getConnection()->rollback();
            $this->debugSection('Database', 'Transaction cancelled; all changes reverted.');
            $db->getDriver()->getConnection()->disconnect();
        }
        $this->container = null;
        $_SESSION = $_FILES = $_GET = $_POST = $_COOKIE = $_REQUEST = [];
    }

    /**
     * Resolves the service based on its configuration from Phalcon's DI container
     * Recommended to use for unit testing.
     *
     * @param string $service    Service name
     * @param array  $parameters Parameters [Optional]
     * @return mixed
     * @part services
     */
    public function grabServiceFromContainer($service, array $parameters = [])
    {
        if (!$this->container->has($service)) {
            $this->fail("Service $service is not available in container");
        }
        return $this->container->make($service, $parameters);
    }

    /**
     * Registers a service in the services container and resolve it. This record will be erased after the test.
     * Recommended to use for unit testing.
     *
     * ``` php
     * <?php
     * $filter = $I->addServiceToContainer('filter', ['className' => '\Phalcon\Filter']);
     * $filter = $I->addServiceToContainer('answer', function () {
     *      return rand(0, 1) ? 'Yes' : 'No';
     * }, true);
     * ?>
     * ```
     *
     * @param string $name
     * @param mixed $definition
     * @part services
     */
    public function addServiceToContainer($name, $definition): void
    {
        try {
            $this->container->set($name, $definition);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
