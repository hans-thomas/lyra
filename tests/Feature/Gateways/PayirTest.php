<?php

	namespace Hans\Lyra\Tests\Feature\Gateways;

	use Hans\Lyra\Gateways\Payir;
	use Hans\Lyra\Tests\TestCase;
	use Illuminate\Support\Str;

	class PayirTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 */
		public function request(): void {
			$instance = new Payir( 10000, 'sandbox' );

			self::assertIsString( $instance->request() );
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function pay(): void {
			$instance = new Payir( 10000, 'sandbox' );

			self::assertIsString( $url = $instance->pay() );
			$response = $this->client->get( $url )->getBody()->getContents();
			self::assertStringContainsString( 'درگاه تست Pay.ir', $response );
		}

		/**
		 * @test
		 *
		 * @return void
		 */
		public function verifyOnSuccess(): void {
			$instance = new Payir( 10000, 'sandbox' );
			$url      = $instance->pay();
			$token    = Str::afterLast( $url, '/' );

			request()->merge( [ 'status' => 1, 'token' => $token ] );

			self::assertTrue( $instance->verify() );
		}

	}