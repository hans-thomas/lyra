<?php

namespace Hans\Lyra\Tests\Feature\Gateways\Contracts;

interface GatewayTest
{
    function request(): void;

    function requestWithInvalidSettings(): void;

    function pay(): void;

    function verifyOnSuccess(): void;

    function verifyOnDuplicateVerification(): void;

    function verifyOnFailed(): void;
}