<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $filename
 * @property string $body
 */
class LibraryDoc extends Model
{
    protected $fillable = ['title', 'filename', 'body'];

    /** @return HasMany<DocChunk, $this> */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocChunk::class)->orderBy('position');
    }
}
