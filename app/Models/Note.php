<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;
use App\Models\Historique;
class Note extends Model
{
    use HasFactory;
    protected $fillable = [
        'note',
        'type_note',
        'date_note',
    ];
    public function evaluation(){
        return $this->belongsTo(Evaluation::class);
    }
    public function historiques()
    {
        return $this->hasMany(Historique::class);
    }

}
