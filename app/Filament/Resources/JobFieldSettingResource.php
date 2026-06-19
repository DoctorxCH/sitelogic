<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobFieldSettingResource\Pages;
use App\Models\JobFieldSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class JobFieldSettingResource extends Resource
{
    protected static ?string $model = JobFieldSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Job Field Settings';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                        $operation === 'create' ? $set('key', Str::slug($state, '_')) : null
                    ),
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn (string $context) => $context === 'edit')
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Text Input',
                        'number' => 'Number Input',
                    ])
                    ->default('text')
                    ->required(),
                Forms\Components\Toggle::make('is_required')
                    ->label('Required field')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\IconColumn::make('is_required')->boolean()->label('Required'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobFieldSettings::route('/'),
            'create' => Pages\CreateJobFieldSetting::route('/create'),
            'edit' => Pages\EditJobFieldSetting::route('/{record}/edit'),
        ];
    }
}
