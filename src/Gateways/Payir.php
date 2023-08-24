<?php

	namespace Hans\Lyra\Gateways;

	use GuzzleHttp\Exception\ClientException;
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
			// TODO: Store token on DB
			$token = $this->request();

			return str_replace(
				':token',
				$token,
				$this->apis()[ 'payment' ]
			);
		}

		public function verify(): bool {
			$status = request( 'status' );
			$token  = request( 'token' );

			if ( $status != 1 ) {
				// User canceled the purchase
				return false;
			}

			if ( $this->isSandboxEnabled() ) {
				return true;
			}

			// TODO: Compare $token with stored token on pay stage

			$data = [
				'api'   => $this->settings[ 'api' ],
				'token' => $token
			];

			try {
				$response = $this->client->post(
					$this->apis()[ 'verification' ],
					[
						'json'    => $data,
						'headers' => [
							'Accept' => 'application/json',
						],
					]
				)
				                         ->getBody()
				                         ->getContents();
				$result   = json_decode( $response, true );
			} catch ( ClientException $e ) {
				return false;
			}

			if ( $result[ 'status' ] !== 1 ) {
				return false;
			}

			// TODO: Store transId on DB

			return true;
		}

		public function errorsList(): array {
			// TODO: Implement errorsList() method.
		}
	}