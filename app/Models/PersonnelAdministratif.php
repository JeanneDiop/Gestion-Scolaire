<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class PersonnelAdministratif extends Model
{
    use HasFactory;
    protected $fillable = [
        'poste',
        'image',
        'date_embauche',
        'statut_emploie',
        'type_salaire',
        'date_naissance',
        'lieu_naissance',
        'numero_CNI',
        'statut_marital',
        'numero_securite_social',
        'date_fin_contrat',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
