<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Parcours;
class Programme extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'niveau_education',
        'credits',
        'date_debut',
        'date_fin',
    ];

    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function parcours()
    {
        return $this->hasMany(Parcours::class);
    }
}
