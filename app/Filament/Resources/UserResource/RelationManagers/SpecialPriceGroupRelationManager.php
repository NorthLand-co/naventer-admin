<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\SpecialPricesGroup;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class SpecialPriceGroupRelationManager extends RelationManager
{
    protected static string $relationship = 'specialPricesGroups';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->color('secondary')
                    ->outlined(),
                Action::make('joinAction')
                    ->label('Add an existing')
                    ->form([
                        Select::make('specialPricesGroups')
                            ->label('Special Prices Group')
                            ->options(SpecialPricesGroup::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $specialPricesGroup = SpecialPricesGroup::findOrFail($data['specialPricesGroups']);
                        if (count($livewire->ownerRecord->specialPricesGroups->where('id', $specialPricesGroup->id)) === 0) {
                            $livewire->ownerRecord->specialPricesGroups()->attach($specialPricesGroup->id);
                            Notification::make()
                                ->title('Special Prices Group added successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Special Prices Group already exists')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('detach')
                    ->label('Detach')
                    ->color('rose')
                    ->action(function ($record, $livewire) {
                        $livewire->ownerRecord->specialPricesGroups()->detach($record->id);
                        Notification::make()
                            ->title('Special Prices Group detached successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([]);
    }
}
