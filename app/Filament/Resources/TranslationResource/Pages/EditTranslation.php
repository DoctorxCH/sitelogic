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

        foreach ($translations as $translation) {
            $data["lang_{$translation->language_code}"] = $translation->value;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $group = $data['group'];
        $key = $record->key; // key is disabled, use existing

        foreach ($data as $field => $value) {
            if (str_starts_with($field, 'lang_')) {
                $languageCode = str_replace('lang_', '', $field);

                Translation::updateOrCreate(
                    [
                        'group' => $group,
                        'key' => $key,
                        'language_code' => $languageCode,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }

        return $record;
    }
}
