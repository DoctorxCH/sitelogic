<?php

namespace App\Filament\Imports;

use App\Models\Job;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class JobImporter extends Importer
{
    protected static ?string $model = Job::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('pid')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('adresse')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('projekt_typ')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('bauleiter')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('technologie')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('asset_ids')
                ->label('Asset-IDs')
                ->rules(['nullable']),
            ImportColumn::make('flat_ids')
                ->label('Flat-IDs')
                ->rules(['nullable']),
            ImportColumn::make('kabel_bep_muffentypen')
                ->label('Kabel-/BEP-/Muffentypen')
                ->rules(['nullable']),
            ImportColumn::make('asset_metadaten')
                ->label('Asset Metadaten (JSON)')
                ->rules(['nullable']),
        ];
    }

    public function resolveRecord(): ?Job
    {
        return Job::firstOrNew([
            'pid' => $this->data['pid'],
        ]);
    }

    protected function afterSave(): void
    {
        // Safely parse JSON or array-like strings from the import
        $parseJson = function($data) {
            if (is_array($data)) return $data;
            if (is_string($data) && !empty($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
                // Fallback if it's a comma separated list instead of valid JSON
                return array_map('trim', explode(',', $data));
            }
            return [];
        };

        $this->record->jobAssets()->updateOrCreate(
            ['job_id' => $this->record->id],
            [
                'asset_ids' => $parseJson($this->data['asset_ids'] ?? null),
                'flat_ids' => $parseJson($this->data['flat_ids'] ?? null),
                'kabel_bep_muffentypen' => $parseJson($this->data['kabel_bep_muffentypen'] ?? null),
                'asset_metadaten' => $parseJson($this->data['asset_metadaten'] ?? null),
            ]
        );
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
