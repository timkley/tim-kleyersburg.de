<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class EvalTool implements Tool
{
    /** @var list<string> */
    private const array BLOCKED_FUNCTIONS = [
        'exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen',
        'pcntl_exec', 'dl',
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'fread',
        'unlink', 'rmdir', 'mkdir', 'rename', 'copy', 'chmod', 'chown',
        'glob', 'scandir', 'readdir', 'opendir',
        'eval', 'assert', 'preg_replace_callback_array',
        'call_user_func', 'call_user_func_array',
        'array_map', 'array_filter', 'array_walk', 'array_walk_recursive',
        'usort', 'uasort', 'uksort',
    ];

    /** @var list<string> */
    private const array BLOCKED_CLASSES = [
        'Storage', 'File', 'Process',
        'Illuminate\\Support\\Facades\\Storage',
        'Illuminate\\Support\\Facades\\File',
        'Illuminate\\Support\\Facades\\Process',
        'Illuminate\\Filesystem',
        'Symfony\\Component\\Process',
        'ReflectionFunction',
        'ReflectionMethod',
        'ReflectionClass',
    ];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Execute PHP code in the Laravel app context. Use for Scout semantic searches (e.g., Quest::search(\'term\')->get()), complex calculations, or HTTP requests. Only allowlisted classes are available — models, Carbon, Collection, Str, Http, math functions.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $code = mb_trim($request['code']);

        $violation = $this->findViolation($code);

        if ($violation !== null) {
            return "Code not allowed: '{$violation}' is blocked for security. Allowed: models, Carbon, Collection, Str, Arr, Http, and math functions.";
        }

        try {
            ob_start();
            $returnValue = eval($code);
            $output = ob_get_clean();

            $result = '';

            if ($output !== '' && $output !== false) {
                $result .= $output;
            }

            if ($returnValue !== null) {
                $formatted = match (true) {
                    is_bool($returnValue) => $returnValue ? 'true' : 'false',
                    is_string($returnValue) => $returnValue,
                    is_numeric($returnValue) => (string) $returnValue,
                    default => json_encode($returnValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: var_export($returnValue, true),
                };

                $result .= ($result !== '' ? "\n" : '').$formatted;
            }

            return $result !== '' ? $result : 'Code executed successfully (no output).';
        } catch (Throwable $e) {
            return "Execution error: {$e->getMessage()}";
        }
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string()->required(),
        ];
    }

    private function findViolation(string $code): ?string
    {
        if (str_contains($code, '`')) {
            return 'backtick operator';
        }

        foreach (self::BLOCKED_FUNCTIONS as $function) {
            if (preg_match('/\b'.preg_quote($function, '/').'\s*\(/', $code)) {
                return $function;
            }
        }

        foreach (self::BLOCKED_CLASSES as $class) {
            if (str_contains($code, $class)) {
                return $class;
            }
        }

        return null;
    }
}
