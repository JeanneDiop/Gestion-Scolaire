<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategorieCours;
class Competence extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',

    ];
    public function categorie(){
        return $this->belongsTo(CategorieCours::class, 'categorie_cours_id');
    }
}
