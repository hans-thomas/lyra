<?php

	namespace Hans\Lyra\Tests\Feature\Gateways;

	use Hans\Lyra\Gateways\Zarinpal;
	use Hans\Lyra\Tests\TestCase;

	class ZarinpalTest extends TestCase {

		/**
		 * @test
		 *
		 * @return void
		 */
		public function request(): void {
			$instance = new Zarinpal( 1000 );
			self::assertIsString( $instance->request() );
		}

	}