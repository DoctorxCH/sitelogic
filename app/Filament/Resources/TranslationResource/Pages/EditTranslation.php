<?php

namespace App\Filament\Resources\TranslationResource\Pages;

use App\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;
use App\Models\Translation;

class EditTranslation extends EditRecord
{
    protected static string $resource = TranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(function ($record) {
                    // Delete all translations for this group and key
                    Translation::where('group', $record->group)
                        ->where('key', $record->key)
                        ->delete();

                    $activeLanguages = \App\Models\Language::where('is_active', true)->pluck('code');
                    foreach ($activeLanguages as $locale) {
                        Translation::generateJsonFile($locale);
                    }

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $group = $this->record->group;
        $key = $this->record->key;

        $translations = Translation::where('group', $group)
            ->where('key', $key)
            ->get();

        $data['translations'] = [];
        foreach ($translations as $translation) {
            $data['translations'][$translation->language_code] = $translation->value;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $value) {
                Translation::updateOrCreate(
                    ['group' => $record->group, 'key' => $record->key, 'language_code' => $locale],
                    ['value' => $value]
                );
            }

            // Generate files only after all DB updates are completed
            $activeLanguages = \App\Models\Language::where('is_active', true)->pluck('code');
            foreach ($activeLanguages as $locale) {
                Translation::generateJsonFile($locale);
            }
        }

        return $record;
    }
}
