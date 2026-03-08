<?php

namespace App\Support;

use App\Models\TicketCommentRead;
use Illuminate\Support\Carbon;

class MarkCommentRead
{
    public static function byUser(int $userId, array $commentIds): void
    {
        $now = Carbon::now();
        foreach (array_unique($commentIds) as $cid) {
            TicketCommentRead::updateOrCreate(
                ['comment_id' => $cid, 'user_id' => $userId],
                ['read_at' => $now]
            );
        }
    }
}
