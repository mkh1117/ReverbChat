<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class chatAttachment extends Model
{

public function chat()
{
    return $this->belongsTo(Chat::class);
}

}
