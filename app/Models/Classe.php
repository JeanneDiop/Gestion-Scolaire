<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salle;
use App\Models\Apprenant;
use App\Models\Planifiercour;
use App\Models\EnseignantClasse;
use App\Models\ApprenantClasse;

use App\Models\Enseignant;

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
    public function apprenants()
    {
        return $this->hasMany(Apprenant::class);
    }

    public function enseignants()
    {
        return $this->hasMany(Enseignant::class);
    }
    public function planifiercours()
    {
        return $this->hasMany(Planifiercour::class);
    }


 public function enseignantclasses()
    {
        return $this->hasMany(EnseignantClasse::class);
    }


 public function apprenantclasses()
 {
     return $this->hasMany(ApprenantClasse::class);
 }
}
