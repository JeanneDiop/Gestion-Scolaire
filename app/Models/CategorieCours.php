<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Competence;
use App\Models\Historique;
class CategorieCours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'leçons',
        'type_exercices',
        'volume_horaire',
        'duree_seance',
        'mode_evaluation',
        'frequence_evaluation',
        'heure_debut',
        'heure_fin',
        'bareme'

    ];
    public function cours(){
        return $this->belongsTo(Cours::class,'cours_id');
    }
    public function competences()
    {
        return $this->hasMany(Competence::class, 'categorie_cours_id');
    }
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
}
