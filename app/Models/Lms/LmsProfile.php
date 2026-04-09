<?php

namespace App\Models\Lms;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LmsProfile extends Model
{
    protected $table = 'lms_profiles';

    public const DIRECTION_MANAGEMENT = 'management';

    public const DIRECTION_SPECIALISTS = 'specialists';

    public const DIRECTION_ENTREPRENEURS = 'entrepreneurs';

    public const DIRECTIONS = [
        self::DIRECTION_MANAGEMENT,
        self::DIRECTION_SPECIALISTS,
        self::DIRECTION_ENTREPRENEURS,
    ];

    public const DIRECTION_LABELS = [
        self::DIRECTION_MANAGEMENT => 'Управленческая команда города',
        self::DIRECTION_SPECIALISTS => 'Специалисты',
        self::DIRECTION_ENTREPRENEURS => 'Предприниматели',
    ];

    public const FACULTY_MANAGEMENT_TEAM = 'management_team';

    public const FACULTY_INDUSTRIAL_TOURISM_GUIDE = 'industrial_tourism_guide';

    public const FACULTY_EXCURSION_ACTIVITY = 'excursion_activity';

    public const FACULTY_TOURISM_PRODUCT = 'tourism_product';

    public const FACULTY_SERVICE = 'service';

    public const FACULTY_TOURISM_INFRASTRUCTURE = 'tourism_infrastructure';

    public const FACULTY_MARKETING = 'marketing';

    public const FACULTIES = [
        self::FACULTY_MANAGEMENT_TEAM,
        self::FACULTY_INDUSTRIAL_TOURISM_GUIDE,
        self::FACULTY_EXCURSION_ACTIVITY,
        self::FACULTY_TOURISM_PRODUCT,
        self::FACULTY_SERVICE,
        self::FACULTY_TOURISM_INFRASTRUCTURE,
        self::FACULTY_MARKETING,
    ];

    public const FACULTY_LABELS = [
        self::FACULTY_MANAGEMENT_TEAM => 'Управленческая команда города',
        self::FACULTY_INDUSTRIAL_TOURISM_GUIDE => 'Гид-экскурсовод промышленного туризма',
        self::FACULTY_EXCURSION_ACTIVITY => 'Экскурсионная деятельность',
        self::FACULTY_TOURISM_PRODUCT => 'Турпродукт',
        self::FACULTY_SERVICE => 'Сервис',
        self::FACULTY_TOURISM_INFRASTRUCTURE => 'Инфраструктура для туризма',
        self::FACULTY_MARKETING => 'Маркетинг',
    ];

    public const DIRECTION_FACULTIES = [
        self::DIRECTION_MANAGEMENT => [
            self::FACULTY_MANAGEMENT_TEAM,
        ],
        self::DIRECTION_SPECIALISTS => [
            self::FACULTY_INDUSTRIAL_TOURISM_GUIDE,
            self::FACULTY_EXCURSION_ACTIVITY,
        ],
        self::DIRECTION_ENTREPRENEURS => [
            self::FACULTY_TOURISM_PRODUCT,
            self::FACULTY_SERVICE,
            self::FACULTY_TOURISM_INFRASTRUCTURE,
            self::FACULTY_MARKETING,
        ],
    ];

    public const FACULTY_ENROLLMENT_TEMPLATES = [
        self::FACULTY_MANAGEMENT_TEAM => 'management_municipal_projects.doc',
        self::FACULTY_INDUSTRIAL_TOURISM_GUIDE => 'industrial_tourism_guide.doc',
        self::FACULTY_EXCURSION_ACTIVITY => 'excursion_activity.doc',
        self::FACULTY_TOURISM_PRODUCT => 'entrepreneurial_projects.doc',
        self::FACULTY_SERVICE => 'entrepreneurial_projects.doc',
        self::FACULTY_TOURISM_INFRASTRUCTURE => 'entrepreneurial_projects.doc',
        self::FACULTY_MARKETING => 'entrepreneurial_projects.doc',
    ];

    protected static function booted(): void
    {
        static::created(function (LmsProfile $profile) {
            $mandatoryCourses = LmsCourse::where('lms_event_id', $profile->lms_event_id)
                ->where('is_mandatory', true)
                ->where('is_active', true)
                ->get();

            foreach ($mandatoryCourses as $course) {
                LmsCourseEnrollment::firstOrCreate(
                    ['lms_course_id' => $course->id, 'user_id' => $profile->user_id],
                    ['status' => 'enrolled']
                );
            }
        });
    }

    protected $fillable = [
        'user_id',
        'lms_event_id',
        'role',
        'lms_role_id',
        'status',
        'invite_token',
        'invited_at',
        'activated_at',
        'position',
        'phone',
        'city',
        'city_id',
        'avatar',
        'organization',
        'project_description',
        'preferred_channel',
        'direction',
        'faculty',
        'direction_approved_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'activated_at' => 'datetime',
            'direction_approved_at' => 'datetime',
        ];
    }

    public function isDirectionApproved(): bool
    {
        return $this->direction && $this->faculty && $this->direction_approved_at !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(LmsEvent::class, 'lms_event_id');
    }

    public function lmsRole(): BelongsTo
    {
        return $this->belongsTo(LmsRole::class, 'lms_role_id');
    }

    /**
     * Доступ к backoffice LMS для конкретного мероприятия: кастомная роль со slug admin
     * либо legacy-поле role=admin при отсутствии lms_role_id.
     */
    public static function userIsLmsAdminForEvent(User $user, LmsEvent $event): bool
    {
        $profile = static::where('user_id', $user->id)
            ->where('lms_event_id', $event->id)
            ->with('lmsRole:id,slug')
            ->first();

        if (! $profile) {
            return false;
        }

        if ($profile->lms_role_id !== null) {
            return $profile->lmsRole?->slug === 'admin';
        }

        return $profile->role === 'admin';
    }

    /** Профиль с правами администрирования LMS (для маршрутов без параметра event). */
    public static function userHasAnyLmsAdminProfile(User $user): bool
    {
        return static::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereHas('lmsRole', function ($role) {
                    $role->where('slug', 'admin');
                })->orWhere(function ($q2) {
                    $q2->whereNull('lms_role_id')->where('role', 'admin');
                });
            })
            ->exists();
    }

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LmsProfileDocument::class);
    }

    public function documentReplaceRequests(): HasMany
    {
        return $this->hasMany(LmsProfileDocumentReplaceRequest::class, 'lms_profile_id');
    }

    public function generateInviteToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('invite_token', $token)->exists());

        $this->update(['invite_token' => $token]);

        return $token;
    }

    public function isProfileComplete(): bool
    {
        return count($this->getMissingFields()) === 0;
    }

    public function getMissingFields(): array
    {
        $missing = [];
        $user = $this->user;

        if (! $user || ! $user->last_name) {
            $missing[] = 'Фамилия';
        }
        if (! $user || ! $user->first_name) {
            $missing[] = 'Имя';
        }
        if (! $user || ! $user->email) {
            $missing[] = 'Email';
        }
        if (! $this->phone) {
            $missing[] = 'Телефон';
        }
        if (! $this->city) {
            $missing[] = 'Город';
        }
        if (! $this->project_description) {
            $missing[] = 'Описание проекта или идеи';
        }
        if (! $this->organization) {
            $missing[] = 'Организация';
        }
        if (! $this->position) {
            $missing[] = 'Должность';
        }

        $docLabels = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление на зачисление',
            LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
            LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
        ];

        $uploadedTypes = $this->documents()
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->pluck('type')
            ->toArray();

        foreach ($docLabels as $type => $label) {
            if (! in_array($type, $uploadedTypes)) {
                $missing[] = $label.' (документ)';
            }
        }

        return $missing;
    }
}
