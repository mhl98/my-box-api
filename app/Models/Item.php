<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $fillable = ['text1', 'text2', 'box_id'];

    //
    public function boxes()
    {
        return $this->belongsTo(Box::class);
    }
}
