<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Evaluation;
use App\Models\Classe;
use App\Models\Note;
use App\Models\Cours;
use App\Models\Enseignant;

class EvaluationApprenant extends Model
{
    protected $table = 'evaluation_apprenants';
    use HasFactory;
    protected $fillable = [
        'apprenant_id',
        'evaluation_id',
        'classe_id',
    ];
    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'apprenant_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
    public function notes()
    {
        return $this->hasMany(Note::class,'evaluation_apprenant_id');
    }
    public function cours()
{
    return $this->belongsTo(Cours::class);
}

public function enseignant()
{
    return $this->belongsTo(Enseignant::class);
}
public function note()
{
    return $this->hasOne(Note::class, 'evaluation_apprenant_id');
}


}
