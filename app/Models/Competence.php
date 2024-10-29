<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',

    ];
    public function cours(){
        return $this->belongsTo(Cours::class, 'cour_id');
    }
}
