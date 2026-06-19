<?php

namespace App\Filament\Resources\JobFieldSettingResource\Pages;

use App\Filament\Resources\JobFieldSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobFieldSettings extends ListRecords
{
    protected static string $resource = JobFieldSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
