<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers\ChecklistsRelationManager;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
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
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', fn ($query) => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['manager', 'super_admin'])))
                            ->label('Assigned Manager')
                            ->placeholder('Select a manager')
                            ->nullable()
                            ->preload(),
                        Forms\Components\Select::make('technician_id')
                            ->relationship('technician', 'name')
                            ->label('Claimed Technician')
                            ->disabled()
                            ->placeholder('No technician has claimed this job yet'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Tracking Data (Automated)')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled(),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'aborted' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('user.name')->label('Manager'),
                Tables\Columns\TextColumn::make('technician.name')->label('Technician'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ChecklistsRelationManager::class,
        ];
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
