<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_comments';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'comment_id'; // <--- CRITICAL FIX: Add this line

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function reads()
    {
        // Assuming TicketCommentRead uses 'comment_id' as the foreign key
        return $this->hasMany(\App\Models\TicketCommentRead::class, 'comment_id', 'comment_id');
    }

    public function hasReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }
    public function scopeUnreadFor($query, int $userId)
    {
        return $query->whereDoesntHave('reads', fn($q) => $q->where('user_id', $userId));
    }
}