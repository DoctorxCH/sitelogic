<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationResource\Pages;
use App\Filament\Resources\TranslationResource\RelationManagers;
use App\Models\Translation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\TextInput::make('group')
                ->required()
                ->default('main'),
            Forms\Components\TextInput::make('key')
                ->required()
                ->disabledOn('edit'),
        ];

        // Ensure we handle migration situations smoothly where the table might not exist
        try {
            $activeLanguages = \App\Models\Language::where('is_active', true)->get();
            foreach ($activeLanguages as $language) {
                $schema[] = Forms\Components\Textarea::make("translations.{$language->code}")
                    ->label("Translation ({$language->name})")
                    ->columnSpanFull();
            }
        } catch (\Exception $e) {
            // Log or ignore if the table is missing
        }

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        $activeLanguages = \App\Models\Language::where('is_active', true)->pluck('code');
                        foreach ($activeLanguages as $locale) {
                            Translation::generateJsonFile($locale);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            $activeLanguages = \App\Models\Language::where('is_active', true)->pluck('code');
                            foreach ($activeLanguages as $locale) {
                                Translation::generateJsonFile($locale);
                            }
                        }),
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
            'index' => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit' => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }
}
