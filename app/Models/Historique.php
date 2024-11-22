<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Note;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Evaluation;
use App\Models\Presence;
use App\Models\Programme;
use App\Models\Salle;
use App\Models\Competence;
use App\Models\CategorieCours;
use App\Models\Evenement;
use App\Models\Employe;
class Historique extends Model
{
    use HasFactory;
    protected $table = 'historiques';

    protected $fillable = [
       'programme_id',
        'cours_id',
        'action',
       'classe_id',
        'message', 
        'user_id',
        'evaluation_id',
        'salle_id',
        'presence_id',
        'competence_id',
        'evenement_id',
        'categorie_cours_id',
        'evaluation_id',
        'note_id',
        'created_at',

    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function note()
    {
        return $this->belongsTo(Note::class);
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
    public function categorie()
    {
        return $this->belongsTo(CategorieCours::class);
    }
    public function competence()
    {
        return $this->belongsTo(Competence::class);
    }
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }
    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
