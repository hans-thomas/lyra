<?php

	namespace Hans\Lyra\Tests\Unit;

	use Hans\Lyra\Models\Invoicable;
	use Hans\Lyra\Models\Invoice;
	use Hans\Lyra\Tests\Core\Factories\PostFactory;
	use Hans\Lyra\Tests\Core\Factories\ProductFactory;
	use Hans\Lyra\Tests\Core\Models\Post;
	use Hans\Lyra\Tests\Core\Models\Product;
	use Hans\Lyra\Tests\TestCase;
	use Illuminate\Support\Str;

	class InvoiceModelTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 */
		public function createWithNoParam(): void {
			$model = $this->makeInvoice();

			self::assertInstanceOf( Invoice::class, $model );
			self::assertIsInt( $model->number );
			self::assertLessThan( 65535, $model->number );
			self::assertNull( $model->token );
			self::assertNull( $model->transaction_id );
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function createWithParams(): void {
			$model = $this->makeInvoice( [
				'token'          => $token = Str::random(),
				'transaction_id' => $transId = Str::random(),
			] );

			self::assertIsString( $model->token );
			self::assertEquals( $token, $model->token );

			self::assertIsString( $model->transaction_id );
			self::assertEquals( $transId, $model->transaction_id );
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function itemsRelationship(): void {
			$model = $this->makeInvoice();

			$product = $this->makeProduct();
			$post    = $this->makePost();

			self::assertEmpty( $model->items );

			$product->invoices()->attach( $model->id );
			$post->invoices()->attach( $model->id );
			$model->refresh();

			self::assertCount( 2, $model->items );

			self::assertInstanceOf( Invoicable::class, $model->items[ 0 ] );
			self::assertEquals(
				$model->items[ 0 ]->toArray(),
				[
					"invoice_id"      => 1,
					"invoicable_type" => "Hans\Lyra\Tests\Core\Models\Product",
					"invoicable_id"   => 1,
					"created_at"      => null,
					"updated_at"      => null,
				]
			);

			self::assertInstanceOf( Invoicable::class, $model->items[ 1 ] );
			self::assertEquals(
				$model->items[ 1 ]->toArray(),
				[
					"invoice_id"      => 1,
					"invoicable_type" => "Hans\Lyra\Tests\Core\Models\Post",
					"invoicable_id"   => 1,
					"created_at"      => null,
					"updated_at"      => null,
				]
			);
		}

		protected function makeInvoice( array $data = [] ): Invoice {
			return Invoice::query()->create( $data );
		}

		protected function makeProduct( array $data = [] ): Product {
			return ProductFactory::new()->create( $data );
		}

		protected function makePost( array $data = [] ): Post {
			return PostFactory::new()->create( $data );
		}

	}