<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Evenement extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'titre',
        'description',
        'lieu',
        'recurrence',
        'ressource',
        'responsable_id',
        'type_evenement',

    ];
    //public function user()
    //{
        //return $this->belongsTo(User::class);
    //}
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'evenement_users', 'evenement_id', 'user_id')
        ->withPivot('classe_id');

    }
}

