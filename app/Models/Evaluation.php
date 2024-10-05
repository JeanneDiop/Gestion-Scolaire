<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Cours;
use App\Models\Note;
class Evaluation extends Model
{
    protected $fillable = [
        'nom_evaluation',
        'niveau_education',
        'categorie',
        'type_evaluation',
        'date_evaluation',
    ];
    use HasFactory;

    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }
    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
