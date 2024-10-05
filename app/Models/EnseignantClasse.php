<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enseignant;
use App\Models\Classe;
class EnseignantClasse extends Model
{
    use HasFactory;
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }
    public function classe(){
        return $this->belongsTo(Classe::class);
    }
}
