<?php

	use Hans\Lyra\Models\Invoice;
	use Hans\Lyra\Tests\Core\Models\Product;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class() extends Migration {
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up(): void {
			Schema::create( ( new Product )->getTable(), function( Blueprint $table ) {
				$table->id();
				$table->string( 'name' );
				$table->string( 'brand' );
				$table->timestamps();
			} );
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down(): void {
			Schema::dropIfExists( ( new Product )->getTable() );
		}
	};
