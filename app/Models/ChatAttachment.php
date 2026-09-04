<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class chatAttachment extends Model
{

    use HasFactory;

    protected $table = 'chat_attachments';

    protected $fillable = [
        'chat_id',
        'room_id',
        'file_path',
        'original_name',
        'file_type',
        'mime_type',
        'file_size',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'file_size' => 'integer',
    ];

public function chat()
{
    return $this->belongsTo(Chat::class);
}

}
