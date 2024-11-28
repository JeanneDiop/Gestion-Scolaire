<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Evaluation;
use App\Models\Classe;
use App\Models\Note;
class EvaluationApprenant extends Model
{
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
}
