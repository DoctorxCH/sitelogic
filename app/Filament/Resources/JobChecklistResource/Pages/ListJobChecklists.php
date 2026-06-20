<?php

namespace App\Filament\Resources\JobChecklistResource\Pages;

use App\Filament\Resources\JobChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobChecklists extends ListRecords
{
    protected static string $resource = JobChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
