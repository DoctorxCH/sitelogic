<?php

namespace App\Filament\Resources;

use App\Models\BepType;
use App\Filament\Resources\BepTypeResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BepTypeResource extends Resource
{
    protected static ?string $model = BepType::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'BEP Typen';

    protected static ?string $label = 'BEP Typ';

    protected static ?string $pluralLabel = 'BEP Typen';

    protected static ?string $navigationGroup = 'Einstellungen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpan('full'),

                Forms\Components\TextInput::make('number_of_units')
                    ->label('Anzahl Nutzeeinheiten')
                    ->integer()
                    ->default(1)
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktiv')
                    ->default(true)
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('number_of_units')
                    ->label('Nutzeeinheiten')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt am')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->nullable(),
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

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBepTypes::route('/'),
            'create' => Pages\CreateBepType::route('/create'),
            'edit' => Pages\EditBepType::route('/{record}/edit'),
        ];
    }
}
