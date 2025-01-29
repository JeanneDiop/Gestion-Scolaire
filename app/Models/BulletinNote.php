<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Note;

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
    'evaluation_apprenant_id'
    ];

    public function note(){
        return $this->belongsTo(Note::class);
    }
}
