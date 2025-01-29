<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;


    protected $fillable = [
        'nom_rapport',
        'type_utilisateur',
        'commentaire_enseignant',
        'commentaire_apprenant',
        'date_rapport',
        'apprenant_id',
        'enseignant_id',
    ];
    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }
  
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }
}
