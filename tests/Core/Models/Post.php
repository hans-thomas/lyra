<?php

namespace Hans\Lyra\Tests\Core\Models;

    use Hans\Lyra\Models\Invoice;
    use Hans\Lyra\Tests\Core\Factories\PostFactory;
    use Illuminate\Database\Eloquent\Factories\Factory;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphToMany;

    class Post extends Model
    {
        use HasFactory;

        protected $fillable = [
            'title',
            'content',
        ];

        /**
         * Create a new factory instance for the model.
         *
         * @return Factory<static>
         */
        protected static function newFactory()
        {
            return PostFactory::new();
        }

        public function invoices(): MorphToMany
        {
            return $this->morphToMany(Invoice::class, 'invoicable');
        }
    }
