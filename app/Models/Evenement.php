<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Classe;
use App\Models\Salle;
use App\Models\Historique;
class Evenement extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'titre',
        'description',
        'lieu',
        'recurrence',
        'ressource',
        'responsable_id',
        'type_evenement',

    ];
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    //public function participants()
//{
    //return $this->belongsToMany(User::class, 'evenement_users')
                //->withPivot('user_id', 'classe_id'); // Inclure les colonnes de la table pivot
//}

public function participants()
{
    return $this->belongsToMany(User::class, 'evenement_users', 'evenement_id', 'user_id')
                ->withPivot('classe_id')
                ->withTimestamps();
}
//public function participants()
    //{
       // return $this->belongsToMany(User::class, 'evenement_users', 'evenement_id', 'user_id')
        //->withPivot('classe_id');


    //}
    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'evenement_users', 'evenement_id', 'classe_id')
        ->withPivot('user_id');

    }

    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
}

