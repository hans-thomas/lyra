<?php

namespace Hans\Lyra\Tests\Core\Factories;

    use Hans\Lyra\Tests\Core\Models\Post;
    use Illuminate\Database\Eloquent\Factories\Factory;

    class PostFactory extends Factory
    {
        protected $model = Post::class;

        /**
         * Define the model's default state.
         *
         * @return array<string, mixed>
         */
        public function definition()
        {
            return [
                'title'   => $this->faker->sentence(),
                'content' => $this->faker->paragraph(),
            ];
        }
    }
