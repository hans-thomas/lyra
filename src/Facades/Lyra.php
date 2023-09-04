<?php

namespace Hans\Lyra\Facades;

use Hans\Lyra\LyraService;
use Hans\Lyra\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * @method static LyraService pay(int $amount)
 * @method static string getRedirectUrl()
 * @method static RedirectResponse redirect()
 * @method static LyraService setGateway(string $gateway, int $amount = null, string $mode = null)
 * @method static bool verify()
 * @method static Invoice getInvoice()
 *
 * @see LyraService
 */
class Lyra extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'lyra-service';
    }
}