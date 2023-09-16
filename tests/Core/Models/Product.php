<?php

namespace Hans\Lyra\Tests\Core\Models;

use Hans\Lyra\Models\Invoice;
use Hans\Lyra\Tests\Core\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'brand',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory()
    {
        return new ProductFactory();
    }

    public function invoices(): MorphToMany
    {
        return $this->morphToMany(Invoice::class, 'invoicable');
    }
}
