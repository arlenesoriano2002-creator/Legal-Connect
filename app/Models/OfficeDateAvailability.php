<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeDateAvailability extends Model
{
    protected $fillable = [
        'law_office_id',
        'date',
        'color',
        'description',
    ];

    public function lawOffice()
    {
        return $this->belongsTo(LawOffice::class);
    }
}
