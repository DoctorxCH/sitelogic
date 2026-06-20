<?php

namespace App\Filament\Resources\JobChecklistResource\Pages;

use App\Filament\Resources\JobChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobChecklist extends EditRecord
{
    protected static string $resource = JobChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
