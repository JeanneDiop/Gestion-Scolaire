<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Cours;
use App\Models\Note;
use App\Models\Historique;
use App\Models\Classe;
class Evaluation extends Model
{
    protected $fillable = [
        'nom_evaluation',
        'niveau_education',
        'categorie',
        'type_evaluation',
        'date_evaluation',
        'cours_id',
        'classe_id'
    ];
    use HasFactory;
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }

    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function apprenants()
{
    return $this->belongsToMany(Apprenant::class, 'evaluation_apprenants', 'evaluation_id', 'apprenant_id');
}
public function classes()
{
    return $this->belongsToMany(
        Classe::class,           // Le modèle final auquel on veut accéder
        'evaluation_apprenants',  // Le nom de la table pivot
        'evaluation_id',         // La clé étrangère sur la table pivot pointant vers `Evaluation`
        'classe_id'              // La clé étrangère sur la table pivot pointant vers `Classe`
    );
}
}
