<?php declare(strict_types=1);

namespace Helper;

use Psr\Http\Message\ResponseInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;

final class ResponseEmitter implements EmitterInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @inheritDoc
     */
    public function emit(ResponseInterface $response): bool
    {
        $this->response = $response;

        return true;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
