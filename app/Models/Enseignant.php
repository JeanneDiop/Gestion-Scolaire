<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EnseignantClasse;
use App\Models\ClasseAssociation;
use App\Models\PresenceAbsence;

class Enseignant extends Model
{
    use HasFactory;
    protected $fillable = [
        'specialite',
        'image',
        'statut_marital',
        'date_naissance',
        'lieu_naissance',
        'niveau_ecole',
        'numero_securite_social',
        'statut',
        'montant_salaire',
        'cotisation_salariale',
        'net_payer',
        'numero_CNI',
        'date_embauche',
        'date_fin_contrat'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enseignantclasses()
    {
        return $this->hasMany(EnseignantClasse::class);
    }

    public function presenceAbsences()
    {
        return $this->hasMany(PresenceAbsence::class);
    }

    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'enseignant_id');
    }
}



