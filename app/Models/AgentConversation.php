<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentConversation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class, 'conversation_id');
    }

    protected function casts(): array
    {
        return [
            'summary_generated_at' => 'datetime',
        ];
    }
}
