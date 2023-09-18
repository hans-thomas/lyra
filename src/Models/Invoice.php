<?php

namespace Hans\Lyra\Models;

use Hans\Alicia\Traits\AliciaHandler;
use Hans\Lyra\Helpers\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int        $id
 * @property int        $number
 * @property string     $token
 * @property string     $transaction_id
 * @property string     $gateway
 * @property int        $amount
 * @property Status     $status
 * @property bool       $offline
 * @property Collection $items
 *
 * @method static Builder offline()
 */
class Invoice extends Model
{
    use AliciaHandler;

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
        'offline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status'  => Status::class,
        'offline' => 'bool',
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
     * @param  Builder  $builder
     *
     * @return void
     */
    public function scopeOffline(Builder $builder): void
    {
        $builder->where('offline', 1);
    }

    /**
     * @return $this
     */
    public function setOffline(): self
    {
        $this->offline = true;

        return $this;
    }

    public function isPending(): bool
    {
        return $this->status == Status::PENDING;
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
