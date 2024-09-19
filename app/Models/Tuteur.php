<?php

namespace App\Models;

use App\Models\User;
use App\Models\Apprenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tuteur extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession',
        'numero_CNI',
        'statut_marital',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function apprenant(){
        return $this->hasmany(Apprenant::class);
}
}
