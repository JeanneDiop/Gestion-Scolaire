<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecole;
class Niveau extends Model
{
    use HasFactory;
    protected $table = 'niveaux';
    protected $fillable = [
        'nom',
        'nombre_enseignant',
        'nombre_eleve',
        'nombre_classe',

    ];
    public function ecoles()
    {
        return $this->belongsToMany(Ecole::class, 'niveau_ecole', 'niveau_id', 'ecole_id');
    }
}
