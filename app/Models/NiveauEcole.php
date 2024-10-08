<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Niveau;
use App\Models\Ecole;
class NiveauEcole extends Model
  {
    use HasFactory;
    
    protected $fillable = [
        'ecole_id',
        'niveau_id',


    ];
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }
    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }
}
