<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Course;
use App\Models\Organisation;
use App\Models\CustomerSession;
use App\Models\ProgramType;
use App\Models\CourseType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CustomerImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    protected $organization_id;
    protected $courses;
    protected $organisations;
    protected $sessions;
    protected $programTypes;
    protected $courseTypes;
    protected $seenPhones = [];
    protected $importedCount = 0;
    protected $skippedCount = 0;

    public function __construct($organization_id)
    {
        $this->organization_id = $organization_id;
        $this->loadMasters();
    }

    protected function loadMasters()
    {
        $this->courses = Course::select('id', 'name')->get();
        $this->organisations = Organisation::select('id', 'name')->get();
        $this->sessions = CustomerSession::all();
        $this->programTypes = ProgramType::all();
        $this->courseTypes = CourseType::all();
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Collect all potential phones in this chunk for batch database check
        $phonesInChunk = [];
        foreach ($rows as $row) {
            $rawPhone = $this->extractValue($row, ['phone_number', 'phone', 'mobile', 'mobile_number', 'contact_number']);
            $cleanPhone = $this->cleanPhoneNumber($rawPhone);
            if ($cleanPhone) {
                $phonesInChunk[] = $cleanPhone;
            }
        }

        $phonesInChunk = array_unique($phonesInChunk);

        // Fetch already existing phones from database (both phone and mobile columns)
        $existingDbPhones = [];
        if (!empty($phonesInChunk)) {
            $existingDbPhones = DB::table('users')
                ->where(function ($q) use ($phonesInChunk) {
                    $q->whereIn('phone', $phonesInChunk)
                      ->orWhereIn('mobile', $phonesInChunk);
                })
                ->pluck('phone')
                ->merge(
                    DB::table('users')
                        ->where(function ($q) use ($phonesInChunk) {
                            $q->whereIn('phone', $phonesInChunk)
                              ->orWhereIn('mobile', $phonesInChunk);
                        })
                        ->pluck('mobile')
                )
                ->filter()
                ->unique()
                ->toArray();
        }

        $existingDbPhonesMap = array_flip($existingDbPhones);
        $newRecords = [];

        foreach ($rows as $row) {
            $name = trim((string)$this->extractValue($row, ['name', 'student_name', 'student']));
            $email = trim((string)$this->extractValue($row, ['student_email', 'email', 'email_id']));
            $email = !empty($email) ? $email : null;

            $rawPhone = $this->extractValue($row, ['phone_number', 'phone', 'mobile', 'mobile_number', 'contact_number']);
            $phone = $this->cleanPhoneNumber($rawPhone);

            // Skip if phone is invalid or already exists in DB / current batch
            if (!$phone || isset($existingDbPhonesMap[$phone]) || isset($this->seenPhones[$phone])) {
                $this->skippedCount++;
                continue;
            }

            // Mark phone as seen to prevent duplicates within the file
            $this->seenPhones[$phone] = true;

            // 1. Current Course
            $rawCourse = trim((string)$this->extractValue($row, ['current_course', 'course', 'course_name']));
            [$courseId, $courseText] = $this->resolveCourse($rawCourse);

            // 2. Current University / Organisation
            $rawUniversity = trim((string)$this->extractValue($row, ['current_university', 'university', 'organisation', 'organization', 'institute', 'college']));
            [$universityId, $universityText] = $this->resolveUniversity($rawUniversity);

            // 3. Passing Year / Session
            $rawPassingYear = trim((string)$this->extractValue($row, ['passing_year', 'session', 'passing_session', 'year']));
            $sessionId = $this->resolveSession($rawPassingYear);

            // 4. Current Program Mode / Type
            $rawProgramMode = trim((string)$this->extractValue($row, ['current_program_mode', 'current_program_type', 'program_mode', 'mode', 'course_type', 'program_type']));
            $programTypeId = $this->resolveProgramMode($rawProgramMode);

            $newRecords[] = [
                'name'                   => !empty($name) ? $name : 'Unknown',
                'email'                  => $email,
                'phone'                  => $phone,
                'mobile'                 => $phone,
                'category_id'            => 2,
                'role'                   => 'user',
                'status'                 => 'active',
                'organization_id'        => $this->organization_id,
                'current_course_id'      => $courseId,
                'current_course_text'    => $courseText,
                'current_university_id'  => $universityId,
                'current_university_text'=> $universityText,
                'current_session'        => $sessionId,
                'current_course_type'    => $programTypeId,
                'created_at'             => now(),
                'updated_at'             => now(),
            ];
        }

        if (!empty($newRecords)) {
            Customer::insert($newRecords);
            $this->importedCount += count($newRecords);
        }
    }

    protected function resolveCourse($rawCourse)
    {
        if (empty($rawCourse)) {
            return [null, null];
        }

        $normalized = $this->normalizeString($rawCourse);

        $matched = $this->courses->first(function ($course) use ($normalized) {
            return $this->normalizeString($course->name) === $normalized;
        });

        if ($matched) {
            return [$matched->id, $matched->name];
        }

        return [null, $rawCourse];
    }

    protected function resolveUniversity($rawUniversity)
    {
        if (empty($rawUniversity)) {
            return [null, null];
        }

        $normalized = $this->normalizeString($rawUniversity);

        $matched = $this->organisations->first(function ($org) use ($normalized) {
            return $this->normalizeString($org->name) === $normalized;
        });

        if ($matched) {
            return [$matched->id, $matched->name];
        }

        return [null, $rawUniversity];
    }

    protected function resolveSession($rawPassingYear)
    {
        if (empty($rawPassingYear)) {
            return null;
        }

        $normalized = $this->normalizeString($rawPassingYear);

        $matched = $this->sessions->first(function ($session) use ($normalized, $rawPassingYear) {
            $sessionNormalized = $this->normalizeString($session->name);
            return $sessionNormalized === $normalized || str_contains($sessionNormalized, $normalized);
        });

        if ($matched) {
            return (string)$matched->id;
        }

        // Auto-create session if not present
        try {
            $newSession = CustomerSession::create([
                'name'            => $rawPassingYear,
                'status'          => 'active',
                'organization_id' => $this->organization_id,
            ]);
            $this->sessions->push($newSession);
            return (string)$newSession->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function resolveProgramMode($rawProgramMode)
    {
        if (empty($rawProgramMode)) {
            return null;
        }

        $normalized = $this->normalizeString($rawProgramMode);

        // First check in ProgramType
        $matched = $this->programTypes->first(function ($pt) use ($normalized) {
            return $this->normalizeString($pt->title) === $normalized;
        });

        if ($matched) {
            return (string)$matched->id;
        }

        // Check CourseType as fallback
        $matchedCourseType = $this->courseTypes->first(function ($ct) use ($normalized) {
            return $this->normalizeString($ct->title) === $normalized;
        });

        if ($matchedCourseType) {
            return (string)$matchedCourseType->id;
        }

        return null;
    }

    protected function cleanPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', (string)$phone);

        // Strip country code 91 if prepended to a 10-digit number
        if (strlen($phone) > 10 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }

        if (strlen($phone) < 8) {
            return null;
        }

        return $phone;
    }

    protected function normalizeString($str)
    {
        $str = strtolower(trim((string)$str));
        $str = str_replace(['.', ',', '-', '/', '_'], '', $str);
        return preg_replace('/\s+/', ' ', $str);
    }

    protected function extractValue($row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
