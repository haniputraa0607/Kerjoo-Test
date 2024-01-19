<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveDate extends Model
{
    protected $table = 'leave_dates';

	protected $primaryKey = "id";

    protected $fillable = [
        'id_leave',
        'date',
    ];


    /**
     * Get the user that owns the LeaveDate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Leave::class, 'id_leave', 'id');
    }
}
