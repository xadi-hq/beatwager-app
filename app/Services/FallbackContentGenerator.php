<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\MessageContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FallbackContentGenerator
{
    public function generate(MessageContext $ctx): string
    {
        // Pull structured metadata from lang
        $meta = $this->getMeta($ctx->key);
        $template = $meta['fallback_template'] ?? '';

        // Build flat variable map from ctx data
        $vars = $this->flattenData($ctx->data);

        // Replace {placeholders} in template
        return $this->interpolate($template, $vars);
    }

    private function getMeta(string $key): array
    {
        // e.g., 'wager.announced' -> __('messages.wager.announced') returns array
        return (array) __('messages.' . $key);
    }

    private function interpolate(string $template, array $vars): string
    {
        return preg_replace_callback('/\{([a-zA-Z0-9_\.]+)\}/', function ($matches) use ($vars) {
            $path = $matches[1];
            $value = Arr::get($vars, $path);
            return is_scalar($value) ? (string) $value : '';
        }, $template);
    }

    private function flattenData(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value)) {
                $result += $this->flattenData($value, $fullKey);
            } elseif (is_object($value)) {
                // Extract common properties if models are passed
                $result[$fullKey] = method_exists($value, '__toString') ? (string) $value : ($value->name ?? $value->title ?? '');
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }
}
