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
			$this->client   = new Client;
			if ( $mode and key_exists( $mode, $this->settings[ 'modes' ] ) ) {
				$this->mode = $mode;
			} else {
				$this->mode = $this->settings[ 'mode' ] ?? 'normal';
			}
		}

		abstract public function request(): string;

		abstract public function pay(): string;

		abstract public function verify(): string;

		abstract public function errorsList( int $error ): array;

		protected function apis(): array {
			return $this->settings[ 'modes' ][ $this->mode ] ?? [];
		}

		public function isSandboxEnabled(): bool {
			return $this->mode == 'sandbox';
		}
	}