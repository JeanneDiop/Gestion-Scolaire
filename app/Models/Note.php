<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EvaluationApprenant;
use App\Models\Historique;
class Note extends Model
{
    use HasFactory;
    protected $fillable = [
        'note',
        'type_note',
        'date_note',
    ];
    public function evaluationApprenant(){
        return $this->belongsTo(EvaluationApprenant::class,'evaluation_apprenant_id');
    }
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }
  
}
