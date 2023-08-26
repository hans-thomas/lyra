<?php

	namespace Hans\Lyra\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\HasMany;

	class Invoice extends Model {

		protected $fillable = [
			'number'
		];

		public function items(): HasMany {
			return $this->hasMany( Invoicable::class )->latest();
		}

	}