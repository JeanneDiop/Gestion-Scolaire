<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evenement;
use App\Models\User;
use App\Models\Classe;
class EvenementUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'evenement_id',
        'user_id',
        'classe_id',

    ];

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

}
