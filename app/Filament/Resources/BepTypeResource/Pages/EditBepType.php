<?php

namespace App\Filament\Resources\BepTypeResource\Pages;

use App\Filament\Resources\BepTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBepType extends EditRecord
{
    protected static string $resource = BepTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
