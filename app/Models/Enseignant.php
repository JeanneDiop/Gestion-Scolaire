<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EnseignantClasse;
use App\Models\ClasseAssociation;
use App\Models\Presence;
use App\Models\Cours;
use App\Models\Rapport;

class Enseignant extends Model
{
    use HasFactory;
    protected $fillable = [
        'matiere_enseignée',
        'image',
        'numero_identification_enseignant',
        'date_naissance',
        'lieu_naissance',
        'niveau_enseignant',
        'nationalité',
        'statut_enseignant',
        'date_debut_service',
        'type_contrat',
        'heure_travail_hebdomadaire',
        'numero_CNI',
        'salaire_base',
        'type_salaire',
        'prime_indemnités',
        'cotisation_sociales',
        'part_employeur',
        'retenue_salaire',
        'mode_paiement',
        'banque_domiciliation',
        'numero_RIB',
        'cv_diplomes',
        'contrat_travail',
        'ancienneté',
        'evaluation_performance',
        'commentaires_notes',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enseignantclasses()
    {
        return $this->hasMany(EnseignantClasse::class);
    }
    public function cours()
    {
        return $this->hasMany(Cours::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, 'enseignant_id');
    }

    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'enseignant_id');
    }

    public function rapports()
{
    return $this->hasMany(Rapport::class);
}
}



