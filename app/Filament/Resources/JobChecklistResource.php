<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobChecklistResource\Pages;
use App\Models\JobChecklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class JobChecklistResource extends Resource
{
    protected static ?string $model = JobChecklist::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Review Checklists';
    protected static ?string $navigationGroup = 'Operations';

    public static function table(Table $table): Table
    {
        return $table
            ->query(JobChecklist::query()->whereIn('status', ['submitted', 'rejected', 'approved']))
            ->columns([
                Tables\Columns\TextColumn::make('job.title')->label('Job')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Checklist'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'submitted' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('Review')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn (JobChecklist $record) => $record->status === 'submitted')
                    ->form(function (JobChecklist $record) {
                        $schema = [];
                        foreach ($record->items as $item) {
                            if (in_array($item->status, ['submitted', 'rejected'])) {
                                $schema[] = Forms\Components\Section::make($item->task)
                                    ->schema([
                                        Forms\Components\ViewField::make("photo_{$item->id}")
                                            ->view('filament.components.photo-viewer')
                                            ->viewData(['path' => $item->photo_path]),
                                        Forms\Components\Select::make("decision_{$item->id}")
                                            ->label('Decision')
                                            ->options(['approved' => 'Approve', 'rejected' => 'Reject'])
                                            ->required(),
                                        Forms\Components\TextInput::make("reason_{$item->id}")
                                            ->label('Rejection Reason')
                                            ->hidden(fn (Forms\Get $get) => $get("decision_{$item->id}") !== 'rejected')
                                            ->requiredIf("decision_{$item->id}", 'rejected'),
                                    ])->columns(1);
                            }
                        }
                        return $schema;
                    })
                    ->action(function (JobChecklist $record, array $data) {
                        $allApproved = true;
                        foreach ($record->items as $item) {
                            if (isset($data["decision_{$item->id}"])) {
                                $decision = $data["decision_{$item->id}"];
                                $item->status = $decision;
                                if ($decision === 'rejected') {
                                    $item->rejection_reason = $data["reason_{$item->id}"] ?? 'Rejected by manager';
                                    $allApproved = false;
                                }
                                $item->save();
                            }
                        }
                        $record->status = $allApproved ? 'approved' : 'rejected';
                        $record->reviewer_id = Auth::id();
                        $record->save();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobChecklists::route('/'),
        ];
    }
}
