<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;
use App\Models\Historique;
class Salle extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'capacity',
        'type'
    ];

    public function classes(){
        return $this->hasMany(Classe::class);
}
public function historiques()
{
    return $this->hasMany(Historique::class,'salle_id');
}
}
