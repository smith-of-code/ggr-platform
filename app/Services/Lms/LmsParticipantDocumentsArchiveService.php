<?php

namespace App\Services\Lms;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class LmsParticipantDocumentsArchiveService
{
    private const TYPE_LABELS = [
        LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление',
        LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
        LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
        LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие_ПД',
        LmsProfileDocument::TYPE_NAME_CHANGE_CERTIFICATE => 'Смена_фамилии',
    ];

    /**
     * @param  Collection<int, LmsProfile>  $profiles
     * @return array{path: string, url: string, files_count: int, participants_count: int}
     */
    public function buildAndStore(Collection $profiles, LmsEvent $event): array
    {
        $diskName = (string) config('filesystems.upload_disk', 'public');
        $storage = Storage::disk($diskName);

        $tmpFile = tempnam(sys_get_temp_dir(), 'lms-docs-');
        if ($tmpFile === false) {
            throw new RuntimeException('Не удалось создать временный файл архива.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpFile);
            throw new RuntimeException('Не удалось создать ZIP-архив.');
        }

        $filesCount = 0;
        $participantsWithFiles = 0;
        $usedFolders = [];
        $tempDocPaths = [];

        try {
            foreach ($profiles as $profile) {
                $profile->loadMissing(['user:id,name,last_name,first_name,patronymic,email', 'documents']);

                $documents = $profile->documents->filter(fn (LmsProfileDocument $doc) => $doc->hasFile());
                if ($documents->isEmpty()) {
                    continue;
                }

                $folder = $this->participantFolderName($profile, $usedFolders);
                $usedNamesInFolder = [];
                $addedForParticipant = false;

                foreach ($documents as $doc) {
                    if (! $storage->exists($doc->file_path)) {
                        continue;
                    }

                    $entryName = $this->zipEntryName($folder, $doc, $usedNamesInFolder);
                    $tempPath = $this->copyStorageFileToTemp($storage, $doc->file_path);
                    if ($tempPath === null) {
                        continue;
                    }

                    $tempDocPaths[] = $tempPath;
                    if (! $zip->addFile($tempPath, $entryName)) {
                        continue;
                    }

                    $filesCount++;
                    $addedForParticipant = true;
                }

                if ($addedForParticipant) {
                    $participantsWithFiles++;
                }
            }

            $zip->close();
        } finally {
            $this->unlinkPaths($tempDocPaths);
        }

        if ($filesCount === 0) {
            @unlink($tmpFile);
            throw new RuntimeException('Нет загруженных документов для выгрузки по выбранным фильтрам.');
        }

        $storagePath = sprintf(
            'lms/exports/%s/participant-documents-%s.zip',
            $event->slug,
            now()->format('Y-m-d-His'),
        );

        $stream = fopen($tmpFile, 'rb');
        if ($stream === false) {
            @unlink($tmpFile);
            throw new RuntimeException('Не удалось прочитать временный архив.');
        }

        $storage->put($storagePath, $stream);
        fclose($stream);
        @unlink($tmpFile);

        return [
            'path' => $storagePath,
            'url' => $this->downloadUrl($storage, $storagePath),
            'files_count' => $filesCount,
            'participants_count' => $participantsWithFiles,
        ];
    }

    private function downloadUrl($storage, string $path): string
    {
        try {
            if (method_exists($storage, 'temporaryUrl')) {
                return $storage->temporaryUrl($path, now()->addDay());
            }
        } catch (\Throwable $e) {
            throw new RuntimeException('Не удалось получить ссылку на архив: '.$e->getMessage(), 0, $e);
        }

        return $storage->url($path);
    }

    /**
     * @param  \Illuminate\Contracts\Filesystem\Filesystem  $storage
     */
    private function copyStorageFileToTemp($storage, string $filePath): ?string
    {
        $stream = $storage->readStream($filePath);
        if ($stream === false) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'lms-doc-');
        if ($tempPath === false) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            return null;
        }

        $local = fopen($tempPath, 'w+b');
        if ($local === false) {
            @unlink($tempPath);
            if (is_resource($stream)) {
                fclose($stream);
            }

            return null;
        }

        stream_copy_to_stream($stream, $local);
        fclose($local);
        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! is_file($tempPath) || filesize($tempPath) === 0) {
            @unlink($tempPath);

            return null;
        }

        return $tempPath;
    }

    /**
     * @param  list<string>  $paths
     */
    private function unlinkPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '') {
                @unlink($path);
            }
        }
    }

    /**
     * @param  array<string, true>  $usedFolders
     */
    private function participantFolderName(LmsProfile $profile, array &$usedFolders): string
    {
        $user = $profile->user;
        $parts = array_filter([
            $user?->last_name,
            $user?->first_name,
            $user?->patronymic,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        $base = Str::slug(implode(' ', $parts));
        if ($base === '') {
            $base = 'user-'.$profile->user_id;
        }

        $folder = $base;
        if (isset($usedFolders[$folder])) {
            $folder = $base.'-'.$profile->user_id;
        }

        $usedFolders[$folder] = true;

        return $folder;
    }

    /**
     * @param  array<string, true>  $usedNamesInFolder
     */
    private function zipEntryName(string $folder, LmsProfileDocument $doc, array &$usedNamesInFolder): string
    {
        $typeLabel = self::TYPE_LABELS[$doc->type] ?? $doc->type;
        $ext = pathinfo((string) $doc->original_name, PATHINFO_EXTENSION);
        $fileName = $typeLabel.($ext !== '' ? '.'.$ext : '');

        $entry = $folder.'/'.$fileName;
        if (! isset($usedNamesInFolder[$entry])) {
            $usedNamesInFolder[$entry] = true;

            return $entry;
        }

        $suffix = 2;
        do {
            $altName = $typeLabel.'_'.$suffix.($ext !== '' ? '.'.$ext : '');
            $entry = $folder.'/'.$altName;
            $suffix++;
        } while (isset($usedNamesInFolder[$entry]));

        $usedNamesInFolder[$entry] = true;

        return $entry;
    }
}
