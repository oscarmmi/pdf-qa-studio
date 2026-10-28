<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $library_doc_id
 * @property string $body
 * @property int $position
 * @property-read LibraryDoc $libraryDoc
 */
class DocChunk extends Model
{
    protected $fillable = ['library_doc_id', 'body', 'position'];

    /** @return BelongsTo<LibraryDoc, $this> */
    public function libraryDoc(): BelongsTo
    {
        return $this->belongsTo(LibraryDoc::class);
    }
}
