<?php

namespace App\Filament\Resources\ChecklistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('task')
                    ->label('Aufgabe / Tätigkeit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_checked')
                    ->label('Erledigt'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task')
            ->columns([
                Tables\Columns\TextColumn::make('task')->label('Aufgabe'),
                Tables\Columns\IconColumn::make('is_checked')->label('Status')->boolean(),
                Tables\Columns\TextColumn::make('checked_at')->label('Erledigt am')->dateTime(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
