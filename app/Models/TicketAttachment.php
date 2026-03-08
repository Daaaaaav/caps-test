<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $table = 'ticket_attachments';
    protected $primaryKey = 'attachment_id';

    // Your table shows only created_at with a DB default → no updated_at
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'file_url',
        'file_type',
        'uploaded_by',
        'cloudinary_public_id',
        'bytes',
        'original_filename',
        'created_at',
    ];

    protected $casts = [
        'ticket_id' => 'int',
        'bytes'     => 'int',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}
