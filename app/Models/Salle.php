<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;

class Salle extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'capacity',
        'type'
    ];

    public function classe(){
        return $this->hasmany(Classe::class);
}
}
