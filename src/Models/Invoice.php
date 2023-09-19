<?php

namespace Hans\Lyra\Models;

use Hans\Alicia\Traits\AliciaHandler;
use Hans\Lyra\Helpers\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
     * Just offline records will be return
     *
     * @param  Builder  $builder
     *
     * @return void
     */
    public function scopeOffline(Builder $builder): void
    {
        $builder->where('offline', 1);
    }

    /**
     * Set the current instance as offline
     *
     * @return $this
     */
    public function setAsOffline(): self
    {
        $this->offline = true;

        return $this;
    }

    /**
     * Check if the current instance's status is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status == Status::PENDING;
    }
}
