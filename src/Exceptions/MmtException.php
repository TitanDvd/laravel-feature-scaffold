<?php

namespace MMT\LaravelFeatureScaffold\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use MMT\ApiResponseNormalizer\ApiResponse;
use Throwable;

class MmtException extends Exception
{
    use ApiResponse;

    public function __construct(
        protected string $errorCode,
        protected string $errorMessage,
        protected int $httpStatusCode = 422,
        protected int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($errorMessage, $code, $previous);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function render() : JsonResponse
    {
        return $this->error(
            $this->errorMessage,
            $this->errorCode,
            [],
            $this->httpStatusCode
        );
    }
}