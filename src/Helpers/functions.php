<?php

	use Hans\Lyra\Models\Invoice;

	if ( ! function_exists( 'lyra_config' ) ) {
		function lyra_config( string $key, mixed $default = null ): mixed {
			return config( "lyra.$key", $default );
		}
	}
	if ( ! function_exists( 'generate_unique_invoice_number' ) ) {
		function generate_unique_invoice_number(): int {
			$number = rand( 10000, 65535 );
			if ( Invoice::query()->where( 'number', $number )->exists() ) {
				$number = generate_unique_invoice_number();
			}

			return $number;
		}
	}