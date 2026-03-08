<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCommentRead extends Model
{
    // The table name from your schema image
    protected $table = 'ticket_comment_reads'; 
    protected $primaryKey = 'read_id';
    public $timestamps = true; // Using read_at for timestamp

    protected $fillable = [
        'comment_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the comment associated with the read record.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'comment_id', 'comment_id');
    }

    /**
     * Get the user who read the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}