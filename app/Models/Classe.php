<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salle;
use App\Models\Enseignant;
use App\Models\Apprenant;

class Classe extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'niveau_classe',

    ];


    public function salle(){
        return $this->belongsTo(Salle::class);
    }
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }
    public function apprenants()
    {
        return $this->hasMany(Apprenant::class);
    }
}
