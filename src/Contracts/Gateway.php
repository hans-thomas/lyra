<?php

	namespace Hans\Lyra\Contracts;

	use GuzzleHttp\Client;

	abstract class Gateway {

		protected readonly array $settings;
		protected readonly Client $client;
		protected readonly string $mode;

		public function __construct(
			protected readonly int $amount,
			string $mode = null
		) {
			$this->settings = lyra_config( 'gateways.' . static::class );
			$this->client   = new Client(['http_errors' => false]);
			if ( $mode and key_exists( $mode, $this->settings[ 'modes' ] ) ) {
				$this->mode = $mode;
			} else {
				$this->mode = $this->settings[ 'mode' ] ?? 'normal';
			}
		}

		abstract public function request(): string;

		abstract public function pay(): string;

		abstract public function verify(): bool;

		abstract public function errorsList(): array;

		protected function apis(): array {
			return $this->settings[ 'modes' ][ $this->mode ] ?? [];
		}

		public function isSandboxEnabled(): bool {
			return $this->mode == 'sandbox';
		}

		protected function translateError( int $code, string $default = 'Failed to process the request!' ): string {
			if ( key_exists( $code, $this->errorsList() ) ) {
				return $this->errorsList()[ $code ];
			}

			return $default;
		}
	}