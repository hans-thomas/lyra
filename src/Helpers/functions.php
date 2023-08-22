<?php

	if ( ! function_exists( 'lyra_config' ) ) {
		function lyra_config( string $key, mixed $default = null ): mixed {
			return config( "lyra.$key", $default );
		}
	}