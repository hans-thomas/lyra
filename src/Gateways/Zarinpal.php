<?php

	namespace Hans\Lyra\Gateways;

	use Hans\Lyra\Contracts\Gateway;

	class Zarinpal extends Gateway {

		public function request(): string {
			$data             = array_diff_key(
				$this->settings, array_flip( [ 'mode', 'modes' ] )
			);
			$data[ 'amount' ] = $this->amount;

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
			if ( empty( $result[ 'errors' ] ) and $result[ 'data' ][ 'code' ] == 100 ) {
				return $result[ 'data' ][ 'authority' ];
			}

			throw new \Exception( 'failed' );
		}

		public function pay(): string {
			// TODO: Implement pay() method.
		}

		public function verify(): bool {
			// TODO: Implement verify() method.
		}

		function errorsList(): array {
			// TODO: Implement translateError() method.
		}
	}