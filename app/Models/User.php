<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Tuteur;
use App\Models\Classe;
use App\Models\Apprenant;
use App\Models\Directeur;
use App\Models\Admin;
use App\Models\Enseignant;
use App\Models\Evenement;
use App\Models\Historique;
use App\Models\Vente;
use App\Models\PersonnelAdministratif;
use App\Models\DemandeMaintenance;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use  HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'etat',
        'adresse',
        'genre',
        'role_nom',
    ];
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

     public function role(){
        return $this->belongsTo(Role::class);
    }

    public function tuteur()
    {
        return $this->hasOne(Tuteur::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }
    public function apprenant()
    {
        return $this->hasOne(Apprenant::class);
    }
    public function directeur()
    {
        return $this->hasOne(Directeur::class);
    }
    public function personneladministratif()
    {
        return $this->hasOne(PersonnelAdministratif::class);
    }
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }

    //public function evenements(){
       // return $this->hasMany(Evenement::class);
//}


public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'evenement_users', 'user_id', 'evenement_id')
        ->withPivot('classe_id');

}
public function classes()
{
    return $this->belongsToMany(Classe::class, 'evenement_users', 'user_id', 'classe_id');
}
public function ventes()
{
    return $this->hasMany(Vente::class);
}

public function demandemaintenances()
{
    return $this->hasMany(DemandeMaintenance::class);
}




}
