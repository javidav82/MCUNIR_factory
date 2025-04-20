<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PrintDocumentation extends Model
{
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'document_path',
        'document_name',
        'status'
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
