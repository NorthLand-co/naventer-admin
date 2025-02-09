<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FaqRelationManager extends RelationManager
{
    protected static string $relationship = 'faqs';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')->required(),
            Forms\Components\TextInput::make('order')
                ->numeric()
                ->minValue(1)
                ->maxValue(255)
                ->default(255),
            Forms\Components\Textarea::make('answer')->columnSpanFull()->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                Tables\Columns\TextColumn::make('question')->searchable(),
                Tables\Columns\TextColumn::make('answer'),
                Tables\Columns\TextColumn::make('faqable_type')->label('Type'),
                Tables\Columns\TextColumn::make('faqable_id')->label('Associated ID'),
            ])
            ->filters([
                //
            ])
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
