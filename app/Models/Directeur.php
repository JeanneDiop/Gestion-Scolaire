<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Ecole;

class Directeur extends Model
{
    use HasFactory;
    protected $fillable = [
        'qualification_academique',
        'statut_marital',
        'date_naissance',
        'lieu_naissance',
        'image',
        'numero_CNI',
        'annee_experience',
        'date_prise_fonction',
        'date_embauche',
        'date_fin_contrat'

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ecoles()
    {
        return $this->hasMany(Ecole::class);
    }
}
