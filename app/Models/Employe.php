<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'email',
        'adresse',
        'poste',
        'image',
        'date_embauche',
        'statut_emploie',
        'type_salaire',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'statut_marital',
        'numero_securite_social',
        'date_fin_contrat',

    ];
}
