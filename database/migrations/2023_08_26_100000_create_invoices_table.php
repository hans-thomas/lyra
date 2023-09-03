<?php

use Hans\Lyra\Helpers\Enums\Status;
use Hans\Lyra\Models\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create((new Invoice())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->smallInteger('number'); // Max value: 65535
            $table->string('token', 128)->nullable()->unique();
            $table->string('transaction_id', 256)->nullable()->unique();
            $table->string('gateway');
            $table->unsignedDecimal('amount',10);
            $table->string('status', 32)->default(Status::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists((new Invoice())->getTable());
    }
};
