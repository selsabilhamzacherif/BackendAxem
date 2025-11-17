<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['nomModule', 'semestre'];

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }
}
