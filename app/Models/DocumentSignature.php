<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'code', 'doc_type', 'title', 'summary', 'content_hash', 'signed_by',
    ];

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
