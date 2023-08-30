<?php

namespace Hans\Lyra\Listeners;

    use Hans\Lyra\Events\TransactionIdReceived;

    class StoreTransIdOnDB
    {
        /**
         * Create the event listener.
         */
        public function __construct()
        {
            // ...
        }

        /**
         * Handle the event.
         */
        public function handle(TransactionIdReceived $event): void
        {
            // Access the order using $event->order...
        }
    }
