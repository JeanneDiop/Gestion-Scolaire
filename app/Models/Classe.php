<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salle;
use App\Models\Apprenant;
use App\Models\Planifiercour;
use App\Models\EnseignantClasse;
use App\Models\ApprenantClasse;
use App\Models\ClasseAssociation;
use App\Models\Programme;
use App\Models\Evenement;
use App\Models\Historique;
use App\Models\Enseignant;
use App\Models\Evaluation;

class Classe extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'niveau_education',
        'niveau_classe',

    ];


    public function salle(){
        return $this->belongsTo(Salle::class);
    }
    public function programmes(){
        return $this->hasMany(Programme::class, 'classe_id');
    }


    public function enseignants()
    {
        return $this->hasMany(Enseignant::class);
    }
    public function planifiercours()
    {
        return $this->hasMany(Planifiercour::class);
    }


 public function enseignantclasses()
    {
        return $this->hasMany(EnseignantClasse::class);
    }


 public function apprenantclasses()
 {
     return $this->hasMany(ApprenantClasse::class);
 }

 public function classeassociations()
 {
     return $this->hasMany(ClasseAssociation::class, 'classe_id');
 }

    //public function evenements()
//{
    //return $this->belongsToMany(Evenement::class, 'evenement_users', 'classe_id', 'evenement_id');
//}
public function evenements()
{
    return $this->belongsToMany(Evenement::class, 'evenement_user')
    ->withPivot('user_id');


}
public function historiques()
{
    return $this->hasMany(Historique::class);
}
public function apprenants()
{
    return $this->belongsToMany(
        Apprenant::class,
        'evaluation_apprenants',
        'classe_id',
        'apprenant_id'
    );
}
public function evaluations()
{
    return $this->belongsToMany(
        Evaluation::class,       // Le modèle final auquel on veut accéder
        'evaluation_apprenants',  // Le nom de la table pivot
        'classe_id',             // La clé étrangère sur la table pivot pointant vers `Classe`
        'evaluation_id'          // La clé étrangère sur la table pivot pointant vers `Evaluation`
    );
}
}
