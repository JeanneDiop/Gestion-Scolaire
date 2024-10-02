<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enseignant;
use App\Models\Planifiercour;
class Cours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'niveau_education',
        'matiere',
        'type',
        'duree',
        'etat',
        'credits',
    ];
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }

    public function planifiercours(){
        return $this->hasMany(Planifiercour::class);
    }
}
