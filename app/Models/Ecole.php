<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Directeur;
use App\Models\Niveau;
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
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'niveau_ecole', 'ecole_id', 'niveau_id');
    }
    public function ecoles()
    {
        return $this->hasMany(Ecole::class, 'niveau_education');
    }
}
