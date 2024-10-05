<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Classe;

class Planifiercour extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_cours',
        'heure_debut',
        'heure_fin',
        'duree',
        'jour_semaine',
        'statut',
        'annee_scolaire',
        'semestre',
    ];
    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function classe(){
        return $this->belongsTo(Classe::class);
    }
}
