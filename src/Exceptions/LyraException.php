<?php

namespace Hans\Lyra\Exceptions;

    use Exception;
    use Throwable;

    class LyraException extends Exception
    {
        private int|string $errorCode;

        public function __construct(string $message, int|string $errorCode, int $responseCode = 500, Throwable $previous = null)
        {
            parent::__construct($message, $responseCode, $previous);
            $this->errorCode = $errorCode;
        }

        public static function make(string $message, int|string $errorCode, int $responseCode = 500, Throwable $previous = null): self
        {
            return new self($message, $errorCode, $responseCode, $previous);
        }

        public function getErrorCode(): int|string
        {
            return $this->errorCode;
        }
    }
