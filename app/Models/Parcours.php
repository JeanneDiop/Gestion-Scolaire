<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Programme;
use App\Models\Apprenant;
class Parcours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'credits',
        'date_creation',
        'date_modification',
    ];
    public function programme(){
        return $this->belongsTo(Programme::class);
    }
    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }
}
