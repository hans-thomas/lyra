<?php

namespace Hans\Lyra\Events;

    use Hans\Lyra\Models\Invoice;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class TransactionIdReceived
    {
        use Dispatchable;
        use InteractsWithSockets;
        use SerializesModels;

        /**
         * Create a new event instance.
         */
        public function __construct(
            public Invoice $invoice,
            public string $transId
        ) {
        }
    }
