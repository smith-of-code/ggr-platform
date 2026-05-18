<?php

namespace App\Services\Lms;

use App\Models\Lms\LmsEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LmsProfileListFilterService
{
    /**
     * @param  Builder|Relation  $query
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder|Relation $query, array $filters, LmsEvent $event): void
    {
        if ($this->filled($filters, 'role_id')) {
            $query->where('lms_role_id', $filters['role_id']);
        }

        if ($this->filled($filters, 'search')) {
            $search = (string) $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($this->filled($filters, 'status')) {
            $query->where('status', $filters['status']);
        }

        if ($this->filled($filters, 'city')) {
            $city = (string) $filters['city'];
            $query->where('city', 'ilike', "%{$city}%");
        }

        if ($this->filled($filters, 'group')) {
            $groupId = $filters['group'];
            $query->whereIn('user_id', function ($q) use ($groupId) {
                $q->select('user_id')
                    ->from('lms_group_members')
                    ->where('lms_group_id', $groupId);
            });
        }

        if ($this->filled($filters, 'course_id')) {
            $courseId = (int) $filters['course_id'];
            if ($courseId > 0) {
                $query->whereIn('user_id', function ($q) use ($event, $courseId) {
                    $q->select('user_id')
                        ->from('lms_course_enrollments')
                        ->where('lms_course_id', $courseId)
                        ->whereIn('status', ['pending', 'enrolled', 'in_progress', 'completed'])
                        ->whereExists(function ($sub) use ($event) {
                            $sub->selectRaw('1')
                                ->from('lms_courses')
                                ->whereColumn('lms_courses.id', 'lms_course_enrollments.lms_course_id')
                                ->where('lms_courses.lms_event_id', $event->id);
                        });
                });
            }
        }

        if ($this->filled($filters, 'program_faculty')) {
            $faculty = trim((string) $filters['program_faculty']);
            if ($faculty !== '') {
                $query->whereIn('user_id', function ($q) use ($event, $faculty) {
                    $q->select('user_id')
                        ->from('lms_course_enrollments')
                        ->where('faculty', $faculty)
                        ->whereIn('status', ['pending', 'enrolled', 'in_progress', 'completed'])
                        ->whereExists(function ($sub) use ($event) {
                            $sub->selectRaw('1')
                                ->from('lms_courses')
                                ->whereColumn('lms_courses.id', 'lms_course_enrollments.lms_course_id')
                                ->where('lms_courses.lms_event_id', $event->id);
                        });
                });
            }
        }

        if ($this->filled($filters, 'docs_no_direction')) {
            $query->whereNull('direction')->whereHas('documents');
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filterKeys(): array
    {
        return ['role_id', 'group', 'search', 'status', 'city', 'docs_no_direction', 'course_id', 'program_faculty'];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function extractFromRequest(\Illuminate\Http\Request $request): array
    {
        return $request->only($this->filterKeys());
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filled(array $filters, string $key): bool
    {
        if (! array_key_exists($key, $filters)) {
            return false;
        }

        $value = $filters[$key];

        return $value !== null && $value !== '';
    }
}
