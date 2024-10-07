<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NiveauEcole;
class Niveau extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'nombre_enseignant',
        'nombre_eleve',
        'nombre_classe',

    ];
    public function niveauecole()
    {
        return $this->hasMany(NiveauEcole::class);
    }
}
