<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Historique;
class Employe extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
        'genre',
        'date_naissance',
        'lieu_naissance',
        'poste_occupé',
        'nationalité',
        'image',
        'numero_CNI',
        'date_debut_service',
        'statut_employé',
        'type_contrat',
        'horaire_travail',
        'numero_identification_employe',
        'superviseur',
        'salaire_base',
        'type_salaire',
        'prime_indemnités',
        'cotisation_sociales',
        'part_employeur',
        'retenue_salaire',
        'mode_paiement',
        'banque_domiciliation',
        'numero_compte_bancaire',
        'cv_diplomes',
        'contrat_travail',
        'ancienneté',
        'certification_formations',
        'evaluation_performance',
        'commentaires_notes',

    ];
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
}

