<?php

namespace App\Filament\Resources\BepTypeResource\Pages;

use App\Filament\Resources\BepTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBepTypes extends ListRecords
{
    protected static string $resource = BepTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
