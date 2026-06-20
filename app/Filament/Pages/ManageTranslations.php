<?php

namespace App\Filament\Pages;

use App\Models\Language;
use App\Models\Translation;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageTranslations extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Translations';

    protected static string $view = 'filament.pages.manage-translations';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormState());
    }

    protected function getFormState(): array
    {
        $translations = Translation::all();
        $languages = Language::where('is_active', true)->get();

        $state = [];
        
        $grouped = $translations->groupBy(function ($item) {
            return ($item->group === 'main' ? '' : $item->group . '.') . $item->key;
        });

        foreach ($grouped as $fullKey => $items) {
            $row = [
                'key' => $fullKey,
            ];
            foreach ($languages as $lang) {
                $item = $items->firstWhere('language_code', $lang->code);
                $row[$lang->code] = $item ? $item->value : '';
            }
            $state[] = $row;
        }

        return [
            'translations' => $state,
        ];
    }

    public function form(Form $form): Form
    {
        $languages = Language::where('is_active', true)->get();
        
        $repeaterFields = [
            TextInput::make('key')
                ->label('Key')
                ->required()
                ->placeholder('e.g., available_jobs')
                ->columnSpan(2),
        ];

        foreach ($languages as $lang) {
            $repeaterFields[] = TextInput::make($lang->code)
                ->label($lang->name . ' (' . ($lang->flag_code ?? $lang->code) . ')')
                ->columnSpan(2);
        }

        return $form
            ->schema([
                Repeater::make('translations')
                    ->label('')
                    ->schema($repeaterFields)
                    ->columns(2 + count($languages) * 2)
                    ->itemLabel(fn (array $state): ?string => $state['key'] ?? null)
                    ->createItemButtonLabel('Add Translation Key')
                    ->defaultItems(0)
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState()['translations'] ?? [];
        $languages = Language::where('is_active', true)->get();

        $seenKeys = [];

        foreach ($state as $row) {
            $fullKey = trim($row['key'] ?? '');
            if (empty($fullKey)) {
                continue;
            }

            $parts = explode('.', $fullKey, 2);
            $group = count($parts) === 2 ? $parts[0] : 'main';
            $key = count($parts) === 2 ? $parts[1] : $fullKey;

            $seenKeys[] = [
                'group' => $group,
                'key' => $key,
            ];

            foreach ($languages as $lang) {
                $val = $row[$lang->code] ?? null;

                Translation::updateOrCreate(
                    [
                        'group' => $group,
                        'key' => $key,
                        'language_code' => $lang->code,
                    ],
                    [
                        'value' => $val,
                    ]
                );
            }
        }

        $allDbTranslations = Translation::all();
        foreach ($allDbTranslations as $dbTrans) {
            $found = false;
            foreach ($seenKeys as $seen) {
                if ($seen['group'] === $dbTrans->group && $seen['key'] === $dbTrans->key) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $dbTrans->delete();
            }
        }

        foreach ($languages as $lang) {
            Translation::generateJsonFile($lang->code);
        }

        Notification::make()
            ->title('Translations saved successfully!')
            ->success()
            ->send();
    }
}
