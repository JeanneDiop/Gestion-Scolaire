<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;
use App\Models\Apprenant;
class ApprenantClasse extends Model
{
    use HasFactory;
    protected $fillable = [
        'apprenant_id',
        'classe_id',
    ];

    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'apprenant_id');
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
}
