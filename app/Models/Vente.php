<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CompteComptable;

class Vente extends Model
{
    use HasFactory;
    protected $fillable = [
        'numero_vente',
        'nom_vente',
        'description',
        'prix_vente',
        'unite',
        'quantite_disponible_stock',
        'type_vente',
        'compte_comptable_id',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comptable()
    {
        return $this->belongsTo(CompteComptable::class);
    }
}
