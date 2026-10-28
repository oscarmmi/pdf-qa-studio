<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaTurn extends Model
{
    protected $fillable = ['question', 'answer', 'retrieved_chunk_ids'];

    protected function casts(): array
    {
        return ['retrieved_chunk_ids' => 'array'];
    }
}
