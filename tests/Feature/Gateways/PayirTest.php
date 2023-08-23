<?php

	namespace Hans\Lyra\Tests\Feature\Gateways;

	use Hans\Lyra\Gateways\Payir;
	use Hans\Lyra\Tests\TestCase;

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

	}