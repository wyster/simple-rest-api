<?php declare(strict_types=1);

namespace App\Exception;

use App\Helper\Env;
use Throwable;
use Zend\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait as ZendCommonProblemDetailsExceptionTrait;

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
        if ($this instanceof Throwable) {
            return $this->createDetailFromException();
        }

        return $this->detail;
    }

    /**
     * Port from ZF\ApiProblem::createDetailFromException
     * @see https://github.com/zfcampus/zf-api-problem/blob/master/src/ApiProblem.php#L310
     * Create detail message from an exception.
     *
     * @return string
     */
    protected function createDetailFromException(): string
    {
        if (!Env::isDebug()) {
            return $this->getMessage();
        }
        $message = trim($this->getMessage());
        $this->additional['trace'] = $this->getTrace();
        $previous = [];
        $e = $this->getPrevious();
        while ($e) {
            $previous[] = [
                'code' => (int)$e->getCode(),
                'message' => trim($e->getMessage()),
                'trace' => $e->getTrace(),
            ];
            $e = $e->getPrevious();
        }
        if (count($previous)) {
            $this->additional['exception_stack'] = $previous;
        }
        return $message;
    }
}
