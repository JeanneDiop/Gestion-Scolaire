<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evenement;
use App\Models\User;
class EvenementUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'evenement_id',
        'user_id',

    ];

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'apprenant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'cours_id');
    }
}
