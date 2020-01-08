<?php declare(strict_types=1);

namespace App\Service\Order;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class HttpService
{
    /**
     * @var string
     */
    private string $url;
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private RequestFactoryInterface $requestFactory;

    public function __construct(string $url, ClientInterface $httpClient, RequestFactoryInterface $requestFactory)
    {
        $this->url = $url;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @throws Exception
     */
    public function checkTsItPossibleToPay(): void
    {
        $request = $this->requestFactory->createRequest('GET', $this->url);
        $response = $this->httpClient->sendRequest($request);
        if ($response->getStatusCode() !== StatusCodeInterface::STATUS_OK) {
            throw new Exception('Invalid response status');
        }
    }
}
