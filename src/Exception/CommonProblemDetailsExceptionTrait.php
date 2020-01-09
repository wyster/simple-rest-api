<?php declare(strict_types=1);

namespace App\Exception;

use App\Helper\Env;
use Throwable;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait as ZendCommonProblemDetailsExceptionTrait;

trait CommonProblemDetailsExceptionTrait
{
    use ZendCommonProblemDetailsExceptionTrait;

    /**
     * Retrieve the API-Problem detail.
     *
     * If an exception was provided, creates the detail message from it;
     * otherwise, detail as provided is used.
     *
     * @return string
     */
    public function getDetail(): string
    {
        return $this->getMessage();
    }

    public function getAdditionalData(): array
    {
        $detail = Env::isDebug() && $this instanceof Throwable ? $this->createThrowableDetail($this) : [];
        return array_merge($this->additional, $detail);
    }

    /**
     * @see \Mezzio\ProblemDetails\ProblemDetailsResponseFactory::createThrowableDetail
     *
     * @return array
     */
    private function createThrowableDetail(Throwable $e): array
    {
        $detail = [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ];

        $previous = [];
        while ($e = $e->getPrevious()) {
            $previous[] = [
                'class' => get_class($e),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
        }

        if (! empty($previous)) {
            $detail['stack'] = $previous;
        }

        return ['exception' => $detail];
    }
}
