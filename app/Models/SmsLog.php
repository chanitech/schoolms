<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'batch_id', 'category', 'recipient_name', 'recipient_phone',
        'message', 'status', 'provider_request_id', 'error_message', 'sent_by',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
