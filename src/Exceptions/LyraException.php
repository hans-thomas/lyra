<?php

namespace Hans\Lyra\Exceptions;

use Exception;
use Throwable;

class LyraException extends Exception
{
    /**
     * Related error code of the exception.
     *
     * @var int|string
     */
    private int|string $errorCode;

    /**
     * @param string         $message
     * @param int|string     $errorCode
     * @param int            $responseCode
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int|string $errorCode, int $responseCode = 500, Throwable $previous = null)
    {
        parent::__construct($message, $responseCode, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * @param string         $message
     * @param int|string     $errorCode
     * @param int            $responseCode
     * @param Throwable|null $previous
     *
     * @return static
     */
    public static function make(string $message, int|string $errorCode, int $responseCode = 500, Throwable $previous = null): self
    {
        return new self($message, $errorCode, $responseCode, $previous);
    }

    /**
     * Return the error code.
     *
     * @return int|string
     */
    public function getErrorCode(): int|string
    {
        return $this->errorCode;
    }
}
