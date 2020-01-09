<?php declare(strict_types=1);

use App\Application;
use Codeception\Test\Unit;
use Psr\Container\ContainerInterface;

final class ApplicationTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function testConstructor(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        new Application($container);
    }
}
