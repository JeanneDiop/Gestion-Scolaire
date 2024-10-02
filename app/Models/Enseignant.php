<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EnseignantClasse;

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
}



