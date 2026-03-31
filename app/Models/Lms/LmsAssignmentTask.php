<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssignmentTask extends Model
{
    protected $table = 'lms_assignment_tasks';

    protected $fillable = [
        'lms_assignment_id',
        'title',
        'description',
        'response_type',
        'template_file',
        'template_file_name',
        'position',
    ];

    /** @return BelongsTo<LmsAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }
}
