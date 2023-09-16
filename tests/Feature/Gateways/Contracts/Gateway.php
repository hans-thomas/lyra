<?php

namespace Hans\Lyra\Tests\Feature\Gateways\Contracts;

interface Gateway
{
    public function request(): void;

    public function requestWithInvalidSettings(): void;

    public function pay(): void;

    public function verifyOnSuccess(): void;

    public function verifyOnDuplicateVerification(): void;

    public function verifyOnFailed(): void;
}
