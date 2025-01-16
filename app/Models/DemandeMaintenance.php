<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PersonnelAdministratif;
class DemandeMaintenance extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'description',
        'status',
        'niveau_priorite',
        'emplacement',
        'date_demande',
        'demandeur_id',
        'personnel_id',
        'date_resolution', 
        'commentaire', 

    ];

    public function demandeur(){
        return $this->belongsTo(User::class);
    }

    public function personnel(){
        return $this->belongsTo(PersonnelAdministratif::class);
    }
}
