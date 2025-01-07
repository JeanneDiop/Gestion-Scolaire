<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vente;
use App\Models\User;
class CompteComptable extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom_compte_comptable',
        'code_compte_comptable',
        'user_id',

    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }
}
