<?php

namespace App\Support\CustomFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Schema Builder values, shared by the exam module and counselling: the rules for one field's posted value
 * (cf[<field id>]), turning it into what is stored, and the option list typed one per line.
 */
trait FieldValues
{
    private function valueRules(Model $f): array
    {
        $k = "cf.{$f->id}";
        return match ($f->type) {
            'text' => [$k => 'nullable|string|max:500'],
            // Editor HTML (tags, tables) is much longer than the text it shows.
            'rich_text' => [$k => 'nullable|string|max:100000'],
            'number' => [$k => 'nullable|numeric'],
            'date' => [$k => 'nullable|date'],
            'url' => [$k => 'nullable|url|max:500'],
            'dropdown' => [$k => ['nullable', Rule::in($f->options ?? [])]],
            'multi_select' => [$k => 'nullable|array', "$k.*" => [Rule::in($f->options ?? [])]],
            'file' => [$k => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240'],
            'group' => [$k => 'nullable|array|max:50', "$k.*.*" => 'nullable|string|max:500'],
            default => [],
        };
    }

    /** Errors name the tab ("The selected Reporting mode is invalid."), not "cf.5". */
    private function valueNames($fields): array
    {
        $names = [];
        foreach ($fields as $f) {
            $names["cf.{$f->id}"] = $names["cf.{$f->id}.*"] = $f->label;
        }
        return $names;
    }

    /** $dir: where an uploaded file goes on the public disk */
    private function value(Request $request, Model $f, $current, string $dir)
    {
        $k = "cf.{$f->id}";
        switch ($f->type) {
            case 'file':
                if ($request->hasFile($k)) {
                    $path = $request->file($k)->store($dir, 'public');
                    if (is_string($current)) Storage::disk('public')->delete($current);
                    return $path;
                }
                return $current;
            case 'number':
                $v = $request->input($k);
                return ($v === null || $v === '') ? null : $v + 0;
            case 'multi_select':
                return array_values($request->input($k, []));
            case 'rich_text':
                // An emptied editor still posts "<p>&nbsp;</p>": treat it as no value.
                $v = (string) $request->input($k);
                return trim(str_replace("\u{A0}", ' ', html_entity_decode(strip_tags($v)))) === '' ? null : $v;
            case 'group':
                $rows = [];
                foreach ($request->input($k, []) as $row) {
                    $clean = [];
                    foreach (self::groupKeys($f) as $key) {
                        $clean[$key] = filled($row[$key] ?? null) ? $row[$key] : null;
                    }
                    if (array_filter($clean)) $rows[] = $clean;
                }
                return $rows;
            default:
                $v = $request->input($k);
                return ($v === null || $v === '') ? null : $v;
        }
    }

    /** Group sub-field keys, derived from the option labels. */
    public static function groupKeys(Model $f): array
    {
        return array_map(fn ($l) => Str::snake(Str::ascii($l)), $f->options ?? []);
    }

    private function options(string $type, ?string $text): ?array
    {
        if (!in_array($type, ['dropdown', 'multi_select', 'group'])) {
            return null;
        }
        $items = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $text)))));
        return $items ?: null;
    }
}
