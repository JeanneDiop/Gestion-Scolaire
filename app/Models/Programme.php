<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Classe;
use App\Models\Historique;
class Programme extends Model
{
    use HasFactory;
    protected $fillable = [
        'matiere',
        'categorie',
        'competences_essentielles',
        'leçons',
        'type_exercices',
        'volume_horaire',
        'duree_seance',
        'mode_evaluation',
        'bareme',
        'source',
        'heure_debut',
        'heure_fin',
        'niveau_education',
        'niveau_classe',
        'file_name',
        'cycle',
        'annee_scolaire',
        'langue_enseignee',
        'importer_programme',
        'exporter_programme',






    ];

    protected $casts = [
        'competences_essentielles' => 'array',
    ];

    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}
