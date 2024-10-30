<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class PersonnelAdministratif extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_naissance',
        'lieu_naissance',
        'poste_occupé',
        'nationalité',
        'image',
        'numero_CNI',
        'date_debut_service',
        'statut_employé',
        'type_contrat',
        'horaires_travail',
        'numero_identification_employe',
        'superviseur',
        'salaire_base',
        'type_salaire',
        'prime_indemnités',
        'cotisation_sociales',
        'departement_service',
        'part_employeur',
        'retenue_salaire',
        'mode_paiement',
        'banque_domiciliation',
        'numero_compte_bancaire',
        'cv_diplomes',
        'contrat_travail',
        'certification_formations',
        'ancienneté',
        'evaluation_performance',
        'commentaires_notes',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
