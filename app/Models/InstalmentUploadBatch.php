<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One accepted payment sheet uploaded by an admin or data entry operator. */
class InstalmentUploadBatch extends Model
{
    protected $fillable = ['uploaded_by', 'original_file_name', 'stored_path', 'rows_count', 'total_amount'];

    protected $casts = ['total_amount' => 'decimal:2'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    public function rows(): HasMany
    {
        return $this->hasMany(InstalmentUploadRow::class, 'batch_id')->orderBy('row_number');
    }
}
