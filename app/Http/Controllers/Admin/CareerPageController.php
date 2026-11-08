<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The career roadmap page's own texts (settings.career_page_content): the list page's heading, the lead form, read and
 * discuss (its topics and their keywords), the FAQs and SEO. An empty field falls back to the website's default text.
 */
class CareerPageController extends Controller
{
    /** field => [label, kind, help]; kind: text, area, lines (one per line). */
    public const FIELDS = [
        'Page' => [
            'list_title' => ['Heading', 'text', 'e.g. All career roadmaps,'],
            'list_title_em' => ['Heading, highlighted part', 'text', 'e.g. stage by stage'],
            'list_lede' => ['Intro under the heading', 'area', null],
            'meta_title' => ['SEO title', 'text', null],
            'meta_description' => ['SEO description', 'area', null],
        ],
        'Lead form (hero)' => [
            'lead_badge' => ['Badge', 'text', 'e.g. Free · takes 30 seconds'],
            'lead_title' => ['Title', 'text', null],
            'lead_text' => ['Text', 'area', null],
            'trust' => ['Trust points', 'lines', 'One per line'],
            'whatsapp' => ['WhatsApp number', 'text', 'With country code, digits only, e.g. 919876543210'],
        ],
        'Read, ask and discuss' => [
            'learn_title' => ['Title', 'text', null],
            'learn_sub' => ['Text', 'area', null],
            'ask_label' => ['Question box label', 'text', null],
            'ask_placeholder' => ['Question box example', 'text', null],
            'ask_note' => ['Question box note', 'text', null],
        ],
        'FAQs' => [
            'faq_title' => ['Title', 'text', null],
        ],
    ];

    public function edit()
    {
        $content = json_decode((string) DB::table('settings')->value('career_page_content'), true) ?: [];
        return view('admin.career_page.edit', ['content' => $content, 'fields' => self::FIELDS]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'c' => 'nullable|array', 'c.*' => 'nullable|string|max:3000',
            'faqs' => 'nullable|array', 'faqs.*.q' => 'nullable|string|max:300', 'faqs.*.a' => 'nullable|string|max:3000',
            'topics' => 'nullable|array', 'topics.*.label' => 'nullable|string|max:60', 'topics.*.keywords' => 'nullable|string|max:500',
        ]);
        $out = [];
        foreach (self::FIELDS as $group) foreach ($group as $key => [, $kind]) {
            $v = trim((string) $request->input("c.$key", ''));
            if ($v === '') continue;
            $out[$key] = $kind === 'lines' ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $v)))) : $v;
        }
        $out['faqs'] = collect($request->input('faqs', []))->map(fn ($f) => ['q' => trim((string) ($f['q'] ?? '')), 'a' => trim((string) ($f['a'] ?? ''))])
            ->filter(fn ($f) => $f['q'] !== '' && $f['a'] !== '')->values()->all();
        $out['topics'] = collect($request->input('topics', []))->map(fn ($t) => ['label' => trim((string) ($t['label'] ?? '')),
            'keywords' => array_values(array_filter(array_map('trim', explode(',', (string) ($t['keywords'] ?? '')))))])
            ->filter(fn ($t) => $t['label'] !== '' && $t['keywords'])->values()->all();
        foreach (['faqs', 'topics'] as $k) if (!$out[$k]) unset($out[$k]);

        $id = DB::table('settings')->value('id');
        if ($id) DB::table('settings')->where('id', $id)->update(['career_page_content' => json_encode($out, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
        else DB::table('settings')->insert(['career_page_content' => json_encode($out, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.career-roadmap-page.edit')->with('success', 'Career roadmap page saved.');
    }
}
