<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Enums\CategoryAttributeType;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryAttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Attributes Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                    Select::make('type')
                        ->options(CategoryAttributeType::options())
                        ->required()
                        ->reactive(),
                    TextInput::make('order')
                        ->type('number')
                        ->minValue(1)
                        ->maxValue(255)
                        ->default(1),
                    TagsInput::make('values')
                        ->visible(fn ($get) => in_array($get('type'), ['dropdown', 'multi-select']))
                        ->separator(',')
                        ->reorderable()
                        ->columnSpanFull(),
                ])->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('type')->badge(),
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
