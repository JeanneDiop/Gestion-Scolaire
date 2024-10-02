<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;
use App\Models\Enseignant;
class EnseignantClasse extends Model
{
    use HasFactory;
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
}
