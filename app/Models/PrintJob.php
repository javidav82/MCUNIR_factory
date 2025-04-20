<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_name',
        'printer_id',
        'user_id',
        'status',
        'copies',
        'color',
        'double_sided',
        'file_path'
    ];

    protected $casts = [
        'color' => 'boolean',
        'double_sided' => 'boolean'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PrintBatch::class, 'batch_id');
    }

    public function statusHistory(): MorphMany
    {
        return $this->morphMany(PrintStatusTracking::class, 'trackable');
    }

    public function documentation(): MorphMany
    {
        return $this->morphMany(PrintDocumentation::class, 'documentable');
    }

    // Validation rules
    public static function rules(): array
    {
        return [
            'batch_id' => 'required|exists:print_batches,id',
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string|max:255',
            'file_type' => 'required|string|max:50',
            'file_size' => 'required|integer|min:0',
            'page_count' => 'required|integer|min:1',
            'status' => 'required|in:pending,processing,completed,failed',
            'error_message' => 'nullable|string',
            'attempts' => 'integer|min:0'
        ];
    }
}
