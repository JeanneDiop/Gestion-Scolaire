<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Classe;
class ProgrammeClasse extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'niveau_education',
        'niveau_classe',
        'cycle',
        'annee_scolaire',
        'langue_enseignee',
        'importer_programme',
        'exporter_programme',

    ];
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}
