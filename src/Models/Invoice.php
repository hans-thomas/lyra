<?php

namespace Hans\Lyra\Models;

use Hans\Lyra\Helpers\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $number
 * @property string $token
 * @property int $transaction_id
 * @property string $gateway
 * @property int $amount
 * @property Status $status
 * @property Collection $items
 */
class Invoice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'number',
        'token',
        'transaction_id',
        'gateway',
        'amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => Status::class,
        'amount' => 'decimal'
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        self::creating(fn (self $model) => $model->number = generate_unique_invoice_number());
    }

    /**
     * Relationship's definition with Invoicable.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Invoicable::class)->latest();
    }
}
