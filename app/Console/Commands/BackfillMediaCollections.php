<?php

namespace App\Console\Commands;

use App\Models\UploadedMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMediaCollections extends Command
{
    protected $signature = 'media:backfill-collections {--dry-run : Show what would be updated without making changes}';
    protected $description = 'Backfill collection/entity_type/entity_id for existing uploaded_media based on entity image fields';

    private int $updated = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $this->backfillDirect('cities', 'App\\Models\\City', 'cities', ['image', 'coat_of_arms'], $dryRun);
        $this->backfillJson('cities', 'App\\Models\\City', 'cities', 'gallery', null, $dryRun);
        $this->backfillJson('cities', 'App\\Models\\City', 'cities', 'attractions', 'image', $dryRun);
        $this->backfillDirect('tours', 'App\\Models\\Tour', 'tours', ['image'], $dryRun);
        $this->backfillJson('tours', 'App\\Models\\Tour', 'tours', 'gallery', null, $dryRun);
        $this->backfillJsonNested('tours', 'App\\Models\\Tour', 'tours', 'accommodations', 'images', $dryRun);
        $this->backfillDirect('blog', 'App\\Models\\Post', 'posts', ['image'], $dryRun);
        $this->backfillDirect('vacancies', 'App\\Models\\Vacancy', 'vacancies', ['image'], $dryRun);
        $this->backfillDirect('recipes', 'App\\Models\\Recipe', 'recipes', ['image'], $dryRun);
        $this->backfillDirect('education_products', 'App\\Models\\EducationProduct', 'education_products', ['image'], $dryRun);
        $this->backfillDirect('directions', 'App\\Models\\Direction', 'directions', ['image'], $dryRun);
        $this->backfillDirect('lms_courses', 'App\\Models\\Lms\\LmsCourse', 'lms_courses', ['image'], $dryRun);

        $this->backfillRelated('lms_grants', 'App\\Models\\Lms\\LmsGrant', 'lms_grant_documents', 'lms_grant_id', ['file_path'], $dryRun);
        $this->backfillRelated('lms_materials', 'App\\Models\\Lms\\LmsMaterialSection', 'lms_material_files', 'lms_material_section_id', ['file_path'], $dryRun);
        $this->backfillDirect('lms_assignments', 'App\\Models\\Lms\\LmsAssignment', 'lms_assignments', ['template_file'], $dryRun);
        $this->backfillDirect('lms_assignments', 'App\\Models\\Lms\\LmsAssignmentTask', 'lms_assignment_tasks', ['template_file'], $dryRun);
        $this->backfillDirect('lms_kb', 'App\\Models\\Lms\\LmsKbItem', 'lms_kb_items', ['file_path'], $dryRun);
        $this->backfillDirect('lms_videos', 'App\\Models\\Lms\\LmsVideo', 'lms_videos', ['file_path', 'thumbnail'], $dryRun);

        $this->backfillAtomsVkusa($dryRun);
        $this->backfillSettings('research_page', 'results_image', $dryRun);

        $this->info("Total updated: {$this->updated}");
        $this->info('Remaining unlinked: ' . UploadedMedia::whereNull('collection')->count());

        return self::SUCCESS;
    }

    private function backfillDirect(string $collection, string $entityType, string $table, array $columns, bool $dryRun): void
    {
        $rows = DB::table($table)->select(array_merge(['id'], $columns))->get();

        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $url = $row->$col;
                if (empty($url)) {
                    continue;
                }
                $this->linkMedia($url, $collection, $entityType, $row->id, $dryRun);
            }
        }
    }

    private function backfillRelated(string $collection, string $entityType, string $table, string $fkColumn, array $columns, bool $dryRun): void
    {
        $rows = DB::table($table)->select(array_merge([$fkColumn], $columns))->get();

        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $url = $row->$col;
                if (empty($url)) {
                    continue;
                }
                $this->linkMedia($url, $collection, $entityType, $row->$fkColumn, $dryRun);
            }
        }
    }

    private function backfillJson(string $collection, string $entityType, string $table, string $jsonCol, ?string $nestedKey, bool $dryRun): void
    {
        $rows = DB::table($table)->select(['id', $jsonCol])->whereNotNull($jsonCol)->get();

        foreach ($rows as $row) {
            $data = json_decode($row->$jsonCol, true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $item) {
                if ($nestedKey) {
                    $url = is_array($item) ? ($item[$nestedKey] ?? null) : null;
                } else {
                    $url = is_string($item) ? $item : null;
                }
                if (empty($url)) {
                    continue;
                }
                $this->linkMedia($url, $collection, $entityType, $row->id, $dryRun);
            }
        }
    }

    private function backfillJsonNested(string $collection, string $entityType, string $table, string $jsonCol, string $nestedArrayKey, bool $dryRun): void
    {
        $rows = DB::table($table)->select(['id', $jsonCol])->whereNotNull($jsonCol)->get();

        foreach ($rows as $row) {
            $data = json_decode($row->$jsonCol, true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $item) {
                if (!is_array($item) || !isset($item[$nestedArrayKey]) || !is_array($item[$nestedArrayKey])) {
                    continue;
                }
                foreach ($item[$nestedArrayKey] as $url) {
                    if (empty($url) || !is_string($url)) {
                        continue;
                    }
                    $this->linkMedia($url, $collection, $entityType, $row->id, $dryRun);
                }
            }
        }
    }

    private function backfillAtomsVkusa(bool $dryRun): void
    {
        $row = DB::table('atoms_vkusa_content')->first();
        if (!$row) {
            return;
        }

        $collection = 'atoms_vkusa';
        $entityType = 'App\\Models\\AtomsVkusaContent';
        $entityId = $row->id;

        if (!empty($row->hero_image)) {
            $this->linkMedia($row->hero_image, $collection, $entityType, $entityId, $dryRun);
        }

        $jsonMaps = [
            'results_gallery' => 'url',
            'results_cases' => 'image',
            'map_cities' => 'recipe_image',
            'partners' => 'logo',
            'reviews' => 'avatar',
            'tourism_help_items' => 'image',
        ];

        foreach ($jsonMaps as $col => $key) {
            if (!isset($row->$col)) {
                continue;
            }
            $data = json_decode($row->$col, true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $item) {
                $url = is_array($item) ? ($item[$key] ?? null) : null;
                if (!empty($url)) {
                    $this->linkMedia($url, $collection, $entityType, $entityId, $dryRun);
                }
            }
        }
    }

    private function backfillSettings(string $collection, string $imageKey, bool $dryRun): void
    {
        $setting = DB::table('settings')
            ->where('group', $collection)
            ->where('key', $imageKey)
            ->first();

        if (!$setting || empty($setting->value)) {
            return;
        }

        $this->linkMedia($setting->value, $collection, null, null, $dryRun);
    }

    private function linkMedia(string $url, string $collection, ?string $entityType, ?int $entityId, bool $dryRun): void
    {
        $media = UploadedMedia::where('url', $url)->whereNull('collection')->first();
        if (!$media) {
            return;
        }

        if ($dryRun) {
            $this->line("  [dry-run] #{$media->id} -> {$collection}" . ($entityType ? " / {$entityType}:{$entityId}" : ''));
        } else {
            $media->update([
                'collection' => $collection,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ]);
        }
        $this->updated++;
    }
}
