<?php

namespace Hans\Lyra\Models;

    use Illuminate\Database\Eloquent\Relations\MorphPivot;

    class Invoicable extends MorphPivot
    {
        protected $table = 'invoicables';
    }
