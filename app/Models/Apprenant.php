<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tuteur;
use App\Models\Classe;
use App\Models\Note;
use App\Models\User;
use App\Models\Presence;
use App\Models\Evaluation;
use App\Models\EvaluationApprenant;
use App\Models\Parcours;
use App\Models\ClasseAssociation;
use App\Models\ApprenantClasse;
use App\Models\Rapport;
use App\Models\BulletinNote;

class Apprenant extends Model
{
    use HasFactory;
    protected $table = 'apprenants';
    protected $fillable = [
        'date_naissance',
        'lieu_naissance',
        'numero_CNI',
        'image',
        'numero_identification_eleve',
        'niveau_education',
        'regime_paiement',
        'reduction_bourse',
        'statut_paiement_actuel',
        'references_factures',
        'nationalité',
        'conditions_medicales',
        'contact_urgence',
        'note_resultat_anterieur',
        'evaluations_specifiques',
        'langue_parlee_maison',
        'activités_extraordinaires',
        'remarque_eleve',
        'acte_naissance',
        'autorisation_parentale',
        'année_inscription',
        'niveau_entrée',
        'statut_inscription',
        'transport_scolaire',
        'service_transport',
        'programme_special',
        'tuteur_id',
        'classe_id'
    ];
    public function tuteur(){
        return $this->belongsTo(Tuteur::class);
    }

    public function classe(){
        return $this->belongsTo(Classe::class ,'classe_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id' );
    }
    public function presences()
    {
        return $this->hasMany(Presence::class, 'apprenant_id');
    }
    public function evaluations()
    {
        return $this->belongsToMany(Evaluation::class, 'evaluation_apprenants', 'apprenant_id', 'evaluation_id');
    }

    public function parcours()
    {
        return $this->hasMany(Parcours::class);
    }
    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'apprenant_id');
    }

    public function apprenantclasses()
    {
        return $this->hasMany(ApprenantClasse::class);
    }

    public function evaluationApprenant()
    {
        return $this->hasMany(Note::class, 'evaluation_apprenant_id');
    }
    public function notes()
    {
        return $this->hasManyThrough(Note::class, EvaluationApprenant::class, 'apprenant_id', 'evaluation_apprenant_id', 'id', 'id');
    }
public function evaluationApprenants()
{
    return $this->hasMany(EvaluationApprenant::class, 'apprenant_id');
}

public function rapports()
{
    return $this->hasMany(Rapport::class);
}

public function bulletin()
{
    return $this->hasOne(BulletinNote::class, 'evaluation_apprenant_id');
}

public function bulletins()
{
    return $this->hasMany(BulletinNote::class);
}

}
