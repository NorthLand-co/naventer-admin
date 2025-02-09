<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\CategoryVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('icon'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
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
                        Select::make('category_variant_id')
                            ->label('Variant')
                            ->options(CategoryVariant::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire): void {
                        $categoryVariant = CategoryVariant::findOrFail($data['category_variant_id']);
                        if (! $livewire->ownerRecord->variants()->where('id', $categoryVariant->id)->exists()) {
                            $livewire->ownerRecord->variants()->attach($categoryVariant->id);
                            Notification::make()
                                ->title('CategoryVariant added successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('CategoryVariant already exists')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                // Tables\Actions\DeleteAction::make(),
                DeleteAction::make()
                    ->label('Delete')
                    ->action(function (CategoryVariant $record, $livewire) {
                        // dd($record, $livewire->ownerRecord);
                        $livewire->ownerRecord->variants()->detach($record->id);

                        Notification::make()
                            ->title('Variant deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
