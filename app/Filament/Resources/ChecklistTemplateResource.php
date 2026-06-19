<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistTemplateResource\Pages;
use App\Models\ChecklistTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChecklistTemplateResource extends Resource
{
    protected static ?string $model = ChecklistTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Checklist Templates';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('job_types')
                            ->multiple()
                            ->options([
                                'ftth' => 'FTTH',
                                'ftto' => 'FTTO',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Predefined Checklist Items')
                    ->schema([
                        Forms\Components\HasManyRepeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('task')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(1)
                            ->createItemButtonLabel('Add Task Point'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('job_types')->badge(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Total Tasks'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecklistTemplates::route('/'),
            'create' => Pages\CreateChecklistTemplate::route('/create'),
            'edit' => Pages\EditChecklistTemplate::route('/{record}/edit'),
        ];
    }
}
