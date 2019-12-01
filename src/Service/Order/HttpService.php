<?php declare(strict_types=1);

namespace App\Service\Order;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class HttpService
{
    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var string
     */
    private $url;

    public function __construct(string $url, ClientInterface $httpClient, RequestFactoryInterface $requestFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->url = $url;
    }

    /**
     * @return bool
     * @throws ClientExceptionInterface
     */
    public function checkTsItPossibleToPay(): bool
    {
        $request = $this->requestFactory->createRequest('GET', $this->url);
        $response = $this->httpClient->sendRequest($request);
        return $response->getStatusCode() === StatusCodeInterface::STATUS_OK;
    }
}
