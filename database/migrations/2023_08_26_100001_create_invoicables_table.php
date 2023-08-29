<?php

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
        public function up()
        {
            Schema::create('invoicables', function (Blueprint $table) {
                $table->foreignIdFor(Invoice::class)->constrained()->cascadeOnDelete();
                $table->morphs('invoicable');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('invoicables');
        }
    };
