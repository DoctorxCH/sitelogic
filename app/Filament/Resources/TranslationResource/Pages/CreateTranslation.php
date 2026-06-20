<?php

namespace App\Filament\Resources\TranslationResource\Pages;

use App\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Database\Eloquent\Model;
use App\Models\Translation;

class CreateTranslation extends CreateRecord
{
    protected static string $resource = TranslationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $group = $data['group'] ?? 'main';
        $key = $data['key'];
        $insertedRecord = null;

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $value) {
                $insertedRecord = Translation::updateOrCreate(
                    ['group' => $group, 'key' => $key, 'language_code' => $locale],
                    ['value' => $value]
                );
            }

            // Generate files only after all DB entries are completed
            $activeLanguages = \App\Models\Language::where('is_active', true)->pluck('code');
            foreach ($activeLanguages as $locale) {
                Translation::generateJsonFile($locale);
            }
        }

        return $insertedRecord ?? new Translation();
    }
}
