<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A counselling (or an exam) may have only one cycle per year + session label. A blank session label counts as one
 * value, so "2028" twice is refused while "2028" and "2028 Phase 2" can live side by side. Deleted cycles do not count.
 *
 *   new UniqueCycleYear(CounsellingCycle::class, 'counselling_id', $counsellingId, $request->session_label, $ignoreId)
 */
class UniqueCycleYear implements ValidationRule
{
    public function __construct(
        private string $model,
        private string $parentColumn,
        private int $parentId,
        private ?string $sessionLabel,
        private ?int $ignoreId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $label = trim((string) $this->sessionLabel);
        $exists = ($this->model)::where($this->parentColumn, $this->parentId)
            ->where('year', (int) $value)
            ->where(fn ($q) => $label === ''
                ? $q->whereNull('session_label')->orWhere('session_label', '')
                : $q->whereRaw('LOWER(TRIM(session_label)) = ?', [mb_strtolower($label)]))
            ->when($this->ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            $fail('A ' . trim($value . ' ' . $label) . ' cycle already exists. Open that one, or give this cycle a different session label (e.g. "Phase 2").');
        }
    }
}
