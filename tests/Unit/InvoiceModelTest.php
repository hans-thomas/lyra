<?php

	namespace Hans\Lyra\Tests\Unit;

	use Hans\Lyra\Models\Invoice;
	use Hans\Lyra\Tests\TestCase;
	use Illuminate\Support\Str;

	class InvoiceModelTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 */
		public function createWithNoParam(): void {
			$model = Invoice::query()->create();

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
			$model = Invoice::query()->create( [
				'token'          => $token = Str::random(),
				'transaction_id' => $transId = Str::random(),
			] );

			self::assertIsString( $model->token );
			self::assertEquals( $token, $model->token );

			self::assertIsString( $model->transaction_id );
			self::assertEquals( $transId, $model->transaction_id );
		}

	}