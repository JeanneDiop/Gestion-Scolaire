<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;

class BulletinNote extends Model
{
    use HasFactory;
    protected $fillable =[
    'disciplines',
    'note_devoir',
    'note_composition',
    'moyenne_note',
    'coefficient',
    'moyenne_x',
    'semestre',
    'th',
    'rang_note',
    'appreciation',
    'total',
    'moyenne_eleve',
    'rang_eleve',
    'retards',
    'absences',
    'observations',
    'observation_conseil_professeur',
    'chef_etablissement',
    'apprenant_id'
    ];

    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }

    protected $casts = [
        'disciplines' => 'array',
        'total' => 'array',
    ];


}
