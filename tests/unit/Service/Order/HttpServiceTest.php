<?php declare(strict_types=1);

namespace Model;

use App\Service\Order\HttpService;
use Codeception\Test\Unit;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use UnitTester;
use Http\Mock\Client as HttpMockClient;

class HttpServiceTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    public function testCheckTsItPossibleToPay(): void
    {
        $httpClient = new HttpMockClient();
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(StatusCodeInterface::STATUS_OK);
        $httpClient->addResponse($response);
        $service = new HttpService(
            'http://localhost',
            $httpClient,
            $this->tester->grabServiceFromContainer(RequestFactoryInterface::class)
        );
        $service->checkTsItPossibleToPay();
    }

    public function testCheckTsItPossibleToPayInvalidResponseStatusException(): void
    {
        $this->expectException(Exception::class);

        $httpClient = new HttpMockClient();
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        $httpClient->addResponse($response);
        $service = $this->createHttpService($httpClient);
        $service->checkTsItPossibleToPay();
    }

    public function testCheckTsItPossibleToPayInvalidClientException(): void
    {
        $this->expectException(Exception::class);

        $httpClient = new HttpMockClient();
        $httpClient->addException($this->createMock(ClientExceptionInterface::class));
        $service = $this->createHttpService($httpClient);
        $service->checkTsItPossibleToPay();
    }

    /**
     * @param HttpMockClient $httpClient
     * @return HttpService
     */
    private function createHttpService(HttpMockClient $httpClient): HttpService
    {
        return new HttpService(
            'http://localhost',
            $httpClient,
            $this->tester->grabServiceFromContainer(RequestFactoryInterface::class)
        );
    }
}
