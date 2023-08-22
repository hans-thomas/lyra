<?php

	namespace Hans\Lyra\Contracts;

	interface Gateway {
		function request(): string;

		function pay(): string;

		function verify(): string;

		function translateError( int $error ): string;
	}