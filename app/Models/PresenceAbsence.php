<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Cours;
class PresenceAbsence extends Model
{
    use HasFactory;
    protected $fillable = [
        'absent',
        'present',
        'date_present',
        'date_absent',
        'raison_absence',
    ];
    public function apprenant(){
        return $this->belongsTo(Apprenant::class);
    }
    public function cours(){
        return $this->belongsTo(Cours::class);
    }
}
