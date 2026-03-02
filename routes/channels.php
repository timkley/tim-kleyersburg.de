<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::channel('chopper.conversation.{conversationId}', function ($user, string $conversationId) {
    // For temp UUIDs (new conversations not yet in DB), allow any authenticated user
    $row = DB::table('agent_conversations')->where('id', $conversationId)->first(['user_id']);

    return $row === null || (int) $row->user_id === (int) $user->id;
});
