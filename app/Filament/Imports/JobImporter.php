<?php

namespace App\Filament\Imports;

use App\Models\Job;
use App\Models\JobFieldSetting;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class JobImporter extends Importer
{
    protected static ?string $model = Job::class;

    public static function getColumns(): array
    {
        $columns = [
            ImportColumn::make('title')
                ->label('Title')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('status')
                ->label('Status')
                ->rules(['in:pending,in_progress,completed,aborted']),
            ImportColumn::make('type')
                ->label('Type')
                ->rules(['required', 'in:ftth,ftto']),
            ImportColumn::make('description')
                ->label('Description'),
        ];

        // Intelligentes Mapping: Läd alle dynamischen Felder als CSV-Spalten
        try {
            $settings = JobFieldSetting::all();
            foreach ($settings as $setting) {
                $columns[] = ImportColumn::make("custom_fields.{$setting->key}")
                    ->label($setting->label);
            }
        } catch (\Exception $e) {
            // Ignorieren falls Datenbank beim Setup nicht erreichbar ist
        }

        return $columns;
    }

    public function resolveRecord(): ?Job
    {
        // Erstellt immer einen neuen Job-Eintrag beim Import
        return new Job();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your job import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
