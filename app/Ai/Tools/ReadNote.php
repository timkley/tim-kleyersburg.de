<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class ReadNote implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: read one markdown note by explicit path. Invoke when user says: "Lies Areas/Health/sleep.md", "Oeffne ...", or asks for a specific file content. Do not invoke when: the user first needs a search across notes or a folder listing.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        try {
            return $service->read($request['path']);
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()->required(),
        ];
    }
}
