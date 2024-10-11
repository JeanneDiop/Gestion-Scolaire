<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Cours;
use App\Models\Enseignant;
use App\Models\Classe;
class ClasseAssociation extends Model
{
    use HasFactory;
    protected $fillable = [
        'apprenant_id',
        'cours_id',
        'enseignant_id',
        'classe_id',
    ];

    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'apprenant_id');
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'cours_id');
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
}
