<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\CategoryAttribute;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_attribute_id')
                    ->label('Attribute')
                    ->options(function ($livewire) {
                        $product = $livewire->ownerRecord;
                        $options = [];
                        if ($product) {
                            $categoryAttributes = $product->category->attributes;
                            $selectedAttributes = $product->attributes->pluck('category_attribute_id')->toArray();
                            $options = $categoryAttributes->whereNotIn('id', $selectedAttributes)->pluck('name', 'id')->toArray();
                        } else {
                            $options = CategoryAttribute::all()->pluck('name', 'id')->toArray();
                        }
                        $options[0] = 'Custom';

                        return $options;
                    })
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $attribute = CategoryAttribute::find($state);
                        $type = $attribute->type ?? null;
                        $set('type', $type ? $type->value : null);
                    }),

                Hidden::make('type')
                    ->reactive()
                    ->default(0)
                    ->dehydrated(),

                Hidden::make('value')
                    ->reactive()
                    ->default(0)
                    ->dehydrated(),

                TextInput::make('name')
                    ->label('Custom Attribute Name')
                    ->visible(function (Get $get) {
                        return $get('category_attribute_id') == 0;
                    })
                    ->required(function (Get $get) {
                        return $get('category_attribute_id') == 0;
                    })
                    ->reactive(),

                TextInput::make('value_tnr')
                    ->label('Value')
                    ->required()
                    ->visible(fn (Get $get) => in_array($get('type'), ['text', 'number', 'range']) || $get('category_attribute_id') == 0)
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $set('value', $get('value_tnr'));
                    }),

                Select::make('value_dm')
                    ->label('Value')
                    ->reactive()
                    ->required()
                    ->visible(fn (Get $get) => in_array($get('type'), ['dropdown', 'multi-select']))
                    ->multiple(fn (Get $get) => $get('type') === 'multi-select')
                    ->options(function (Get $get) {
                        $categoryAttribute = CategoryAttribute::find($get('category_attribute_id'));

                        return $categoryAttribute ? array_combine(explode(',', $categoryAttribute->values), explode(',', $categoryAttribute->values)) : [];
                    })
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $value = is_array($get('value_dm')) ? implode(', ', $get('value_dm')) : $get('value_dm');
                        $set('value', $value);
                    }),

                Toggle::make('value_b')
                    ->label('Value')
                    ->required()
                    ->visible(function (Get $get) {
                        $visible = $get('type') === 'boolean';

                        return $visible;
                    })
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $set('value', $get('value_b'));
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('attribute.name')->label('Attribute'),
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('value')->label('Value'),
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
