<?php

	namespace Hans\Lyra\Gateways;

	use Hans\Lyra\Contracts\Gateway;

	class Payir extends Gateway {

		public function request(): string {
			$data             = array_diff_key(
				$this->settings, array_flip( [ 'mode', 'modes' ] )
			);
			$data[ 'amount' ] = $this->amount;
			$data             = array_filter( $data, fn( $item ) => ! empty( $item ) );

			if ( $this->isSandboxEnabled() ) {
				$data[ 'api' ] = 'test';
			}

			$response = $this->client->post(
				$this->apis()[ 'purchase' ],
				[
					'json'    => $data,
					'headers' => [
						'Content-Type' => 'application/json',
					],
				]
			)
			                         ->getBody()
			                         ->getContents();
			$result   = json_decode( $response, true );
			if ( $result[ 'status' ] == 1 ) {
				return $result[ 'token' ];
			}

			throw new \Exception( 'failed' );
		}

		public function pay(): string {
			// TODO: Implement pay() method.
		}

		public function verify(): string {
			// TODO: Implement verify() method.
		}

		public function errorsList( int $error ): array {
			// TODO: Implement errorsList() method.
		}
	}