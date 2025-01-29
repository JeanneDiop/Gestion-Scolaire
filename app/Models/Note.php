<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EvaluationApprenant;
use App\Models\Historique;
use App\Models\Apprenant;
use App\Models\Evaluation;
use App\Models\BulletinNote;
class Note extends Model
{
    use HasFactory;
    protected $fillable = [
        'note',
        'type_note',
        'date_note',
        'semestre',
    ];
    public function evaluationApprenant(){
        return $this->belongsTo(EvaluationApprenant::class,'evaluation_apprenant_id');
    }
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'evaluation_apprenant_id');  // Adaptez le nom de la clé étrangère
    }

public function evaluation()
{
    return $this->belongsTo(Evaluation::class, 'evaluation_apprenant_id');  // Remplacez 'evaluation_apprenant_id' par la clé étrangère appropriée
}

public function bulletin()
{
    return $this->hasOne(BulletinNote::class, 'evaluation_apprenant_id');
}
}
