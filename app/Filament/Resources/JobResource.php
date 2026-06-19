<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use App\Models\JobFieldSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Jobs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Main Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'aborted' => 'Aborted',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'ftth' => 'FTTH',
                                'ftto' => 'FTTO',
                            ])
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', fn ($query) => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['manager', 'super_admin'])))
                            ->label('Assigned Manager')
                            ->placeholder('Select a manager')
                            ->nullable()
                            ->preload(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Dynamic Custom Fields')
                    ->schema(self::getCustomFieldsSchema())
                    ->columns(2)
                    ->visible(fn () => count(self::getCustomFieldsSchema()) > 0),

                Forms\Components\Section::make('Tracking Data (Automated)')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')->disabled(),
                        Forms\Components\TextInput::make('latitude')->numeric()->disabled(),
                        Forms\Components\TextInput::make('longitude')->numeric()->disabled(),
                    ])->columns(2),
            ]);
    }

    protected static function getCustomFieldsSchema(): array
    {
        $fields = [];
        try {
            $settings = JobFieldSetting::all();
            foreach ($settings as $setting) {
                $component = match ($setting->type) {
                    'number' => Forms\Components\TextInput::make("custom_fields.{$setting->key}")->numeric(),
                    default => Forms\Components\TextInput::make("custom_fields.{$setting->key}"),
                };

                $component->label($setting->label)->required($setting->is_required);
                $fields[] = $component;
            }
        } catch (\Exception $e) {
            // Failsafe
        }
        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'pending' => 'gray',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    'aborted' => 'danger',
                }),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Manager'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('direct_csv_import')
                    ->label('Direct CSV Import')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('Select CSV File')
                            ->required()
                            ->disk('local')
                            ->directory('imports')
                            ->visibility('private'),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/' . $data['csv_file']);
                        
                        if (!file_exists($filePath)) {
                            Notification::make()->danger()->title('Import Error')->body('File could not be stored.')->send();
                            return;
                        }

                        $fileContent = file_get_contents($filePath);
                        if (empty($fileContent)) {
                            Notification::make()->danger()->title('Import Error')->body('File is empty.')->send();
                            return;
                        }

                        // Automatische Trennzeichen-Erkennung (Komma oder Semikolon)
                        $separator = ',';
                        if (strpos($fileContent, ';') !== false && (strpos($fileContent, ',') === false || strpos($fileContent, ';') < strpos($fileContent, ','))) {
                            $separator = ';';
                        }

                        if (($handle = fopen($filePath, 'r')) === false) {
                            Notification::make()->danger()->title('Import Error')->body('Cannot open file.')->send();
                            return;
                        }

                        $headers = fgetcsv($handle, 0, $separator);
                        if (!$headers) {
                            fclose($handle);
                            return;
                        }

                        // Bereinigung von unsichtbaren Zeichen / UTF-8 BOM
                        $headers = array_map(fn($h) => trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF"), $headers);

                        $successCount = 0;
                        $skippedCount = 0;

                        while (($row = fgetcsv($handle, 0, $separator)) !== false) {
                            if (count($headers) !== count($row)) {
                                $skippedCount++;
                                continue;
                            }

                            $rowData = array_combine($headers, $row);

                            $jobData = [
                                'title' => $rowData['title'] ?? ($rowData['Title'] ?? 'Untitled Job'),
                                'status' => $rowData['status'] ?? ($rowData['Status'] ?? 'pending'),
                                'type' => strtolower($rowData['type'] ?? ($rowData['Type'] ?? 'ftth')),
                                'description' => $rowData['description'] ?? ($rowData['Description'] ?? null),
                                'custom_fields' => [],
                            ];

                            foreach ($rowData as $key => $value) {
                                $cleanKey = str_replace('custom_fields.', '', $key);
                                $cleanKey = trim($cleanKey, " \t\n\r\0\x0B\xEF\xBB\xBF");
                                
                                if (str_starts_with($key, 'custom_fields.') || JobFieldSetting::where('key', $cleanKey)->exists()) {
                                    $jobData['custom_fields'][$cleanKey] = ($value === '-' || $value === '') ? null : $value;
                                }
                            }

                            Job::create($jobData);
                            $successCount++;
                        }

                        fclose($handle);
                        @unlink($filePath);

                        if ($successCount > 0) {
                            Notification::make()
                                ->success()
                                ->title('Import completed')
                                ->body("Successfully imported $successCount jobs. Skipped $skippedCount rows due to formatting mismatches.")
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Import failed')
                                ->body("No jobs were imported. Checked structure with separator '$separator'.")
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
