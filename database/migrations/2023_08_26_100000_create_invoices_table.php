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
		public function up() {
			Schema::create( ( new Invoice )->getTable(), function( Blueprint $table ) {
				$table->id()->from( 10000 );
				$table->smallIncrements( 'number' )->from( 10000 ); // Max value: 65535
				$table->timestamps();
			} );
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists( ( new Invoice )->getTable() );
		}
	};
