<?php

	namespace Hans\Lyra\Events;

	use Hans\Lyra\Models\Invoice;
	use Illuminate\Broadcasting\InteractsWithSockets;
	use Illuminate\Foundation\Events\Dispatchable;
	use Illuminate\Queue\SerializesModels;

	class TokenReceived {
		use Dispatchable, InteractsWithSockets, SerializesModels;

		/**
		 * Create a new event instance.
		 */
		public function __construct(
			public Invoice $invoice,
			public string $token
		) {
		}
	}