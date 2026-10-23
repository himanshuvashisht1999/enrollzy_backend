<?php

namespace App\Support;

/**
 * What the admin can edit for each homepage section (Homepage Sections → Edit), for the homepage design "v11".
 * The website reads the same keys (frontend App\Support\Home\HomeContent) and uses the design's text for anything
 * left empty.
 *
 *  core   - the section's own columns shown on the form, with their labels: badge (settings.badge_text), title,
 *           subtitle, cta_title, cta_url, image
 *  fields - extra values saved in settings: text, textarea, number, toggle, list (one item per line), icon,
 *           repeater (rows of sub-fields; a repeater can hold another repeater)
 *  data   - where the section's cards come from, linked from the form
 */
class HomepageSectionSchema
{
    /** Icons the website can draw (stages, trust pillars). */
    public const ICONS = [
        'ten' => 'Book (Class 10)', 'twelve' => 'Graduation cap (Class 12)', 'grad' => 'Bag (graduates)', 'work' => 'Growth arrow (working)',
        'parent' => 'People (parents)', 'calendar' => 'Calendar', 'shield' => 'Shield with tick', 'award' => 'Award ribbon', 'book' => 'Open book',
        'star' => 'Star', 'chat' => 'Chat bubble', 'search' => 'Magnifier', 'money' => 'Money', 'globe' => 'Globe',
    ];

    /** Sections of the previous homepage design that the current homepage does not show. */
    public const UNUSED = ['why_choose_us', 'marketplace', 'trending_skills', 'trending_courses', 'video_testimonials', 'student_form', 'ques_ans', 'talk_to_alumni'];

    public static function isUsed(string $key): bool
    {
        return self::for($key) !== null;
    }

    public static function for(string $key): ?array
    {
        $heading = ['title' => 'Heading', 'subtitle' => 'Text under the heading'];
        $button = ['cta_title' => 'Button text', 'cta_url' => 'Button link'];
        $badge = ['badge' => 'Small label above the heading'];
        $multiline = 'Press Enter in the heading for a line break.';

        return match ($key) {
            'hero_banner' => [
                'about' => 'The top of the page: headline, search box, popular searches and the "open right now" cards.',
                'core' => ['badge' => 'Badge next to "DPIIT"', 'title' => 'Headline, before the changing word', 'subtitle' => 'Text under the headline'],
                'fields' => [
                    'rotating_words' => ['type' => 'list', 'label' => 'Changing words in the headline', 'help' => 'One per line. They take turns after the headline, e.g. "Find the right college / course / school".'],
                    'title_after' => ['type' => 'text', 'label' => 'Headline, second line', 'placeholder' => 'and get in.'],
                    'search_placeholder' => ['type' => 'text', 'label' => 'Search box hint'],
                    'popular' => ['type' => 'repeater', 'label' => 'Popular searches (chips under the search box)', 'add' => 'Add a chip', 'fields' => [
                        'label' => ['type' => 'text', 'label' => 'Text'], 'url' => ['type' => 'text', 'label' => 'Link', 'placeholder' => '/search?q=MBA or #online']]],
                    'popular_live_exam' => ['type' => 'toggle', 'default' => true, 'label' => 'Also show the first exam from "Happening now" as a chip'],
                    'assurances' => ['type' => 'list', 'label' => 'Ticks under the search box', 'help' => 'One per line.'],
                    'show_live' => ['type' => 'toggle', 'default' => true, 'label' => 'Show the "open right now" cards (exams and counselling that are open)'],
                ],
                'data' => [['Exams and counselling dates', 'admin/exam-module/exams']],
            ],
            'journey_stages' => [
                'about' => '"Where are you on the road?": a row of stages (After Class 10, After Class 12...), each with its cards.',
                'core' => $badge + $heading,
                'fields' => [
                    'default_stage' => ['type' => 'number', 'label' => 'Stage shown first', 'help' => '1 = the first stage in the list.', 'min' => 1],
                    'stages' => ['type' => 'repeater', 'label' => 'Stages', 'add' => 'Add a stage', 'fields' => [
                        'name' => ['type' => 'text', 'label' => 'Stage name'], 'icon' => ['type' => 'icon', 'label' => 'Icon'],
                        'cards' => ['type' => 'repeater', 'label' => 'Cards', 'add' => 'Add a card', 'fields' => [
                            'title' => ['type' => 'text', 'label' => 'Title'], 'text' => ['type' => 'text', 'label' => 'Short text'],
                            'url' => ['type' => 'text', 'label' => 'Link', 'placeholder' => '/top-exams or #online']]]]],
                ],
            ],
            'happening_now' => [
                'about' => '"Happening now": exam registrations and counselling rounds that are open or coming, and scholarship deadlines. Left out when nothing is open.',
                'core' => $badge + $heading,
                'fields' => [
                    'exams_label' => ['type' => 'text', 'label' => 'Tab name: exams'], 'coun_label' => ['type' => 'text', 'label' => 'Tab name: counselling'],
                    'sch_label' => ['type' => 'text', 'label' => 'Tab name: scholarships'], 'rows' => ['type' => 'number', 'label' => 'Rows per tab', 'min' => 1, 'max' => 10],
                ],
                'data' => [['Exams (Exam Module)', 'admin/exam-module/exams'], ['Scholarships: application dates', 'admin/scholarships']],
            ],
            'university_grid' => [
                'about' => '"Pick a stream": one tab per stream with its top universities, entrance exams and courses.',
                'core' => $heading,
                'data' => [['Streams, their universities, exams and courses', 'admin/homepage-stream-tabs']],
            ],
            'university_comparison' => [
                'about' => '"Compare before you decide": the compare box (same course at other universities, any mix, or courses within one university).',
                'core' => $badge + $heading + ['cta_title' => 'Link text (optional)', 'cta_url' => 'Link (optional)'],
            ],
            'online_degrees' => [
                'about' => 'Online degrees: your text on the left, the most offered online programmes with their lowest fee on the right.',
                'core' => $badge + $heading + ['cta_title' => 'Button text', 'cta_url' => 'Button link (empty = opens the call-back form)'],
                'heading_help' => $multiline . ' {count} = number of universities with online programmes.',
                'fields' => [
                    'points' => ['type' => 'list', 'label' => 'Ticks', 'help' => 'One per line.'],
                    'panel_title' => ['type' => 'text', 'label' => 'Heading of the programme list'],
                    'items' => ['type' => 'number', 'label' => 'Programmes in the list', 'min' => 1, 'max' => 8],
                ],
                'data' => [['Online courses of each university', 'admin/organisations']],
            ],
            'noteworthy_mentions' => [
                'about' => '"In-demand programmes and skills": one tab per category with its cards.',
                'core' => $heading + ['cta_title' => 'Button text (optional)', 'cta_url' => 'Button link (optional)'],
                'data' => [['Tabs (categories)', 'admin/noteworthy-categories'], ['Cards (mentions)', 'admin/noteworthy-mentions']],
            ],
            'specialized_courses' => [
                'about' => 'Scholarships: the scholarships marked "featured on homepage", with level and category filters.',
                'core' => $badge + $heading + ['cta_title' => 'Link text', 'cta_url' => 'Link'],
                'heading_help' => '{count} = number of active scholarships.',
                'data' => [['Scholarships (tick "featured on homepage")', 'admin/scholarships']],
            ],
            'institute_marquee' => [
                'about' => 'Boarding schools: four schools per tab, one tab per board (CBSE, ICSE, IB...).',
                'core' => $heading + $button,
                'data' => [['Schools', 'admin/organisations']],
            ],
            'free_counselling' => [
                'about' => 'Free counselling: your text and steps; the button opens the call-back form. The form\'s words are here too.',
                'core' => $badge + $heading + ['cta_title' => 'Button text', 'cta_url' => 'Button link (empty = opens the call-back form)'],
                'heading_help' => $multiline,
                'fields' => [
                    'points' => ['type' => 'list', 'label' => 'Ticks', 'help' => 'One per line.'],
                    'panel_title' => ['type' => 'text', 'label' => 'Heading of the steps'],
                    'steps' => ['type' => 'repeater', 'label' => 'Steps', 'add' => 'Add a step', 'fields' => [
                        'title' => ['type' => 'text', 'label' => 'Step'], 'text' => ['type' => 'text', 'label' => 'Short text']]],
                    'form_title' => ['type' => 'text', 'label' => 'Call-back form: heading'],
                    'form_text' => ['type' => 'text', 'label' => 'Call-back form: text'],
                    'form_options' => ['type' => 'list', 'label' => 'Call-back form: "I\'m looking for" choices', 'help' => 'One per line. Leave empty to hide the question.'],
                    'mobile_bar' => ['type' => 'toggle', 'default' => true, 'label' => 'Show the Search / counselling bar at the bottom on phones'],
                ],
                'data' => [['Call-back requests (leads)', 'admin/leads']],
            ],
            'school_marquee' => [
                'about' => 'Coaching institutes: four active coaching institutes. Left out when there are none.',
                'core' => $heading + $button,
                'data' => [['Coaching institutes', 'admin/organisations']],
            ],
            'expert_carousel' => [
                'about' => 'Experts: featured experts first.',
                'core' => $heading + $button,
                'fields' => ['items' => ['type' => 'number', 'label' => 'Experts shown', 'min' => 1, 'max' => 12]],
                'data' => [['Experts (tick "featured")', 'admin/experts']],
            ],
            'testimonials' => [
                'about' => '"Why students trust Enrollzy": your points, the latest student feedback and how many universities, schools and scholarships are listed (numbers under 50 are not shown).',
                'core' => $heading + ['cta_title' => 'Button text (optional)', 'cta_url' => 'Button link (optional)'],
                'fields' => [
                    'pillars' => ['type' => 'repeater', 'label' => 'Points', 'add' => 'Add a point', 'fields' => [
                        'icon' => ['type' => 'icon', 'label' => 'Icon'], 'title' => ['type' => 'text', 'label' => 'Title'], 'text' => ['type' => 'text', 'label' => 'Text']]],
                    'show_reviews' => ['type' => 'toggle', 'default' => true, 'label' => 'Show the latest student feedback'],
                    'show_counts' => ['type' => 'toggle', 'default' => true, 'label' => 'Show how many universities, schools and scholarships are listed'],
                ],
                'data' => [['Student feedback', 'admin/testimonials']],
            ],
            'blogs' => [
                'about' => 'Blogs: the latest published blogs.',
                'core' => $heading + $button,
                'fields' => ['items' => ['type' => 'number', 'label' => 'Blogs shown', 'min' => 1, 'max' => 12]],
                'data' => [['Blogs', 'admin/blogs']],
            ],
            'faq' => [
                'about' => 'FAQ: the first questions in the FAQ list.',
                'core' => $heading + $button,
                'fields' => ['items' => ['type' => 'number', 'label' => 'Questions shown', 'min' => 1, 'max' => 20]],
                'data' => [['FAQs', 'admin/faqs']],
            ],
            default => null,
        };
    }

    /**
     * The submitted settings[...] for a section, cleaned to its schema: lists from text, numbers, toggles, repeater rows
     * re-indexed with fully empty rows dropped. Keys not in the schema are ignored.
     */
    public static function clean(array $fields, array $input): array
    {
        $out = [];
        foreach ($fields as $name => $f) {
            $v = $input[$name] ?? null;
            $out[$name] = match ($f['type']) {
                'toggle' => !empty($v) && $v !== '0',
                'number' => $v === null || $v === '' ? null : max($f['min'] ?? 0, min($f['max'] ?? 1000, (int) $v)),
                'list' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $v)), 'strlen')),
                'repeater' => array_values(array_filter(array_map(fn ($row) => is_array($row) ? self::clean($f['fields'], $row) : null, is_array($v) ? $v : []),
                    fn ($row) => $row && collect($row)->contains(fn ($x) => $x !== null && $x !== '' && $x !== [] && $x !== false))),
                default => ($t = trim((string) $v)) === '' ? null : mb_substr($t, 0, 1000),
            };
        }
        return $out;
    }
}
