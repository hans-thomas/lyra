<?php

namespace Hans\Lyra\Exceptions;

class LyraErrorCode
{
    public const GATEWAY_CLASS_NOT_FOUNT = 'LyraEC0';
    public const WRONG_GATEWAY_CLASS_SELECTED = 'LyraEC1';
    public const FAILED_TO_VERIFYING = 'LyraEC2';
    public const TOKEN_MISMATCHED = 'LyraEC3';
    public const AMOUNT_NOT_PASSED = 'LyraEC4';
    public const ORDER_ID_MISMATCHED = 'LyraEC5';
    public const INVOICE_IS_NOT_VALID = 'LyraEC6';
    public const INVOICE_NOT_FOUND_OR_NOT_OFFLINE = 'LyraEC7';
}
