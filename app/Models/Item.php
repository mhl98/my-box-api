<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $fillable = ['title', 'description', 'user_id'];

    //
    public function boxes()
    {
        return $this->belongsTo(Box::class);
    }
}
