<?php

namespace Hans\Lyra\Tests\Core\Factories;

use Hans\Lyra\Tests\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'  => $this->faker->sentence(),
            'brand' => $this->faker->word(),
        ];
    }
}
