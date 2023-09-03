<?php

	namespace Hans\Lyra;

	use Hans\Lyra\Contracts\Gateway;
	use Hans\Lyra\Exceptions\LyraErrorCode;
	use Hans\Lyra\Exceptions\LyraException;
	use Hans\Lyra\Models\Invoice;
	use Illuminate\Http\RedirectResponse;

	class LyraService {

		private Gateway $gateway;
		private Invoice $invoice;
		private string $gatewayRedirectUrl;

		public function __construct() {
			throw_unless(
				$defaultGateway = lyra_config( 'default', false ) and class_exists( $defaultGateway ),
				LyraException::make(
					'Default gateway class is not set!',
					LyraErrorCode::DEFAULT_GATEWAY_NOT_FOUNT
				)
			);
			$this->gateway = $defaultGateway;
			$this->invoice = new Invoice();
		}

		public function pay(): self {
			$token                    = $this->gateway->request();
			$this->invoice->token     = $token;
			$this->gatewayRedirectUrl = $this->gateway->pay( $token );

			return $this;
		}

		public function getRedirectUrl(): string {
			return $this->gatewayRedirectUrl;
		}

		public function redirect(): RedirectResponse {
			return redirect()->away( $this->gatewayRedirectUrl );
		}

		public function __destruct() {
			$this->invoice->save();
		}

	}