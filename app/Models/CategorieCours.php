<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Competence;
class CategorieCours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'leçons',
        'type_exercice',
        'volume_horaire',
        'duree_recommander_sceance',
        'mode_evaluation',
        'bareme',

    ];
    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function competences()
    {
        return $this->hasMany(Competence::class);
    }
}
