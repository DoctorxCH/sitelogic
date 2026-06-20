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

        $firstModel = null;

        // Iterate over dynamic lang fields
        foreach ($data as $field => $value) {
            if (str_starts_with($field, 'lang_')) {
                $languageCode = str_replace('lang_', '', $field);

                $translation = Translation::create([
                    'group' => $group,
                    'key' => $key,
                    'language_code' => $languageCode,
                    'value' => $value,
                ]);

                if (!$firstModel) {
                    $firstModel = $translation;
                }
            }
        }

        // Return a model to satisfy the return type of handleRecordCreation
        // If no languages exist, just create a dummy one or fail gracefully
        if (!$firstModel) {
            $firstModel = Translation::create([
                'group' => $group,
                'key' => $key,
                'language_code' => 'en', // fallback
                'value' => null,
            ]);
        }

        return $firstModel;
    }
}
