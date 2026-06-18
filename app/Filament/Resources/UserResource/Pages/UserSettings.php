<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Spatie\Permission\Models\Permission;

class UserSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = UserResource::class;
    protected static string $view = 'filament.resources.user-resource.pages.user-settings';
    
    public $record;
    public ?array $data = [];

    public function mount($record): void
    {
        $this->record = \App\Models\User::findOrFail($record);
        $this->form->fill([
            'permissions' => $this->record->permissions->pluck('id')->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                CheckboxList::make('permissions')
                    ->label('Berechtigungen für ' . $this->record->name)
                    ->options(Permission::all()->pluck('name', 'id'))
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->record->permissions()->sync($this->data['permissions']);
        Notification::make()->title('Gespeichert')->success()->send();
    }
}
