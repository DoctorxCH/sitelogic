<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistResource\Pages;
use App\Filament\Resources\ChecklistResource\RelationManagers\ItemsRelationManager;
use App\Models\Checklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChecklistResource extends Resource
{
    protected static ?string $model = Checklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Checklists';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('job_id')
                    ->relationship('job', 'title')
                    ->label('Auftrag')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Name der Checkliste')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_completed')
                    ->label('Vollständig abgeschlossen'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job.title')->label('Auftrag')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Checkliste'),
                Tables\Columns\IconColumn::make('is_completed')->label('Abgeschlossen')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Erstellt am')->dateTime(),
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecklists::route('/'),
            'create' => Pages\CreateChecklist::route('/create'),
            'edit' => Pages\EditChecklist::route('/{record}/edit'),
        ];
    }
}
