<?php

	namespace Hans\Lyra\Tests\Core\Models;

	use Hans\Lyra\Models\Invoice;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphToMany;

	class Post extends Model {

		protected $fillable = [
			'title',
			'description',
		];

		public function invoices(): MorphToMany {
			return $this->morphToMany( Invoice::class, 'invoicable' );
		}
	}