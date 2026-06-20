<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Translation;
use App\Models\Language;

class ScanTranslations extends Command
{
    protected $signature = 'i18n:scan';
    protected $description = 'Scan views and app files for translation keys and insert them into the database';

    public function handle(): void
    {
        $directories = [resource_path('views'), app_path()];
        // Sucht nach __('group.key'), trans('group.key') oder @lang('group.key')
        $pattern = '/(?:__|trans|@lang)\(\s*[\'"]([^\'"]+)[\'"]/';
        $keys = [];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                continue;
            }

            $files = File::allFiles($directory);
            foreach ($files as $file) {
                if (preg_match_all($pattern, $file->getContents(), $matches)) {
                    foreach ($matches[1] as $match) {
                        $keys[] = trim($match);
                    }
                }
            }
        }

        $keys = array_unique($keys);
        $activeLocales = Language::where('is_active', true)->pluck('code');
        $added = 0;

        foreach ($keys as $fullKey) {
            $parts = explode('.', $fullKey, 2);
            $group = count($parts) === 2 ? $parts[0] : 'main';
            $key = count($parts) === 2 ? $parts[1] : $fullKey;

            foreach ($activeLocales as $locale) {
                $exists = Translation::where('group', $group)
                    ->where('key', $key)
                    ->where('language_code', $locale)
                    ->exists();

                if (!$exists) {
                    Translation::create([
                        'group' => $group,
                        'key' => $key,
                        'language_code' => $locale,
                        'value' => null
                    ]);
                    $added++;
                }
            }
        }

        foreach ($activeLocales as $locale) {
            Translation::generateJsonFile($locale);
        }

        $this->info("Scan abgeschlossen. {$added} neue Übersetzungs-Einträge generiert.");
    }
}