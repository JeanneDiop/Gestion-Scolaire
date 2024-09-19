<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tuteur;
use App\Models\Classe;
use App\Models\User;

class Apprenant extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_naissance',
        'lieu_naissance',
        'numero_CNI',
        'numero_carte_scolaire',
        'statut_marital',
        'tuteur_id',
        'classe_id'
    ];
    public function tuteur(){
        return $this->belongsTo(Tuteur::class);
    }

    public function classe(){
        return $this->belongsTo(Classe::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
