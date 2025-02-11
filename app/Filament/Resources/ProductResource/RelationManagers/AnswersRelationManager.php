<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\RecommendationAnswer;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('question.title'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelect(
                        fn (Select $select) => $select
                            ->placeholder('Select a post')
                            ->options(function () {
                                $currentItem = $this->getOwnerRecord();
                                $options = RecommendationAnswer::all()->load(['question']);
                                $attachedIds = $currentItem->answers->pluck('id');
                                $filteredOptions = $options->reject(function ($option) use ($attachedIds) {
                                    return $attachedIds->contains($option->id);
                                });

                                return $filteredOptions->mapWithKeys(function ($option) {
                                    return [
                                        $option->id => $option->title.' - '.$option->question->title,
                                    ];
                                });
                            }),
                    )
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
