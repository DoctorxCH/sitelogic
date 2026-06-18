<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistResource\Pages;
use App\Filament\Resources\ChecklistResource\RelationManagers;
use App\Models\Checklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChecklistResource extends Resource
{
    protected static ?string $model = Checklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('auftragskartei_id')
                    ->relationship('job', 'pid')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in work' => 'In Work',
                        'completed' => 'Completed',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'storno' => 'Storno',
                        'error' => 'Error',
                    ])
                    ->default('open')
                    ->required(),
                Forms\Components\Toggle::make('hauptschalter')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job.pid')->label('Job PID')->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\BooleanColumn::make('hauptschalter'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
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
