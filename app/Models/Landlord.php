<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landlord extends Model {

        protected $table = 'landlords';

        protected $guarded = array('id');

        public function internal()
        {
                return $this->hasOne(LandlordInternals::class, 'landlords_id');
        }

        public function bank()
        {
                return $this->hasOne(LandlordBanks::class, 'landlords_id');
        }

        public function attachment()
        {
                return $this->hasOne(LandlordAttachments::class, 'landlords_id');
        }
}