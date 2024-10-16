<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Cours;
use App\Models\Enseignant;
class PresenceAbsence extends Model
{
    use HasFactory;
    protected $fillable = [
        'type_utilisateur',
        'statut',
        'date_present',
        'date_absent',
        'heure_arrivee',
        'duree_retard',
        'raison_absence',
        'apprenant_id',
        'cours_id',
        'enseignant_id',
    ];
    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }
    public function cours(){
        return $this->belongsTo(Cours::class);
    }
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }
}
