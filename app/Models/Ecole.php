<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Directeur;
use App\Models\NiveauEcole;
class Ecole extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'email',
        'siteweb',
        'anne_creation',
        'type_ecole',
        'niveau_education',

    ];
    public function directeur()
    {
        return $this->belongsTo(Directeur::class);
    }
    public function niveauecole()
    {
        return $this->hasMany(NiveauEcole::class);
    }
    public function ecoles()
    {
        return $this->hasMany(Ecole::class, 'niveau_education');
    }
}
