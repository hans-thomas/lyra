<?php

	namespace Hans\Lyra\Listeners;

	use Hans\Lyra\Events\TokenReceived;

	class StoreTokenOnDB {
		/**
		 * Create the event listener.
		 */
		public function __construct() {
			// ...
		}

		/**
		 * Handle the event.
		 */
		public function handle( TokenReceived $event ): void {
			// Access the order using $event->order...
		}
	}