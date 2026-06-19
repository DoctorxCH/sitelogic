<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistItemResource\Pages;
use App\Models\ChecklistItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChecklistItemResource extends Resource
{
    protected static ?string $model = ChecklistItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    
    protected static ?string $navigationLabel = 'Checklisten-Punkte';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('checklist_id')
                    ->relationship('checklist', 'name')
                    ->label('Checkliste')
                    ->required(),
                Forms\Components\TextInput::make('task')
                    ->label('Aufgabe / Tätigkeit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_checked')
                    ->label('Erledigt'),
                Forms\Components\DateTimePicker::make('checked_at')
                    ->label('Abgehakt am')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('checklist.name')
                    ->label('Checkliste')
                    ->searchable(),
                Tables\Columns\TextColumn::make('task')
                    ->label('Aufgabe'),
                Tables\Columns\IconColumn::make('is_checked')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('checked_at')
                    ->label('Erledigt am')
                    ->dateTime()
                    ->sortable(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecklistItems::route('/'),
            'create' => Pages\CreateChecklistItem::route('/create'),
            'edit' => Pages\EditChecklistItem::route('/{record}/edit'),
        ];
    }
}
