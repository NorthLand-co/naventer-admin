<?php

namespace App\Filament\Resources\UserOrderResource\RelationManagers;

use App\Models\CategoryVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_variant_id')
            ->columns([
                TextColumn::make('product.name')->label('Product Name'),
                TextColumn::make('product.category.name')->label('Product Name'),
                TextColumn::make('quantity')->label('Quantity'),
                TextColumn::make('item_info')
                    ->label('Details')
                    ->formatStateUsing(function ($state) {
                        $itemInfo = json_decode($state, true);
                        $categoryVariantIds = collect($itemInfo['variant']['items'])
                            ->pluck('category_variant_id')
                            ->unique();

                        $categoryVariants = CategoryVariant::whereIn('id', $categoryVariantIds)->get()->keyBy('id');

                        $updatedItemsArray = collect($itemInfo['variant']['items'])->map(function ($item) use ($categoryVariants) {
                            $item['category_variant'] = $categoryVariants->get($item['category_variant_id']);

                            return $item;
                        })->toArray();

                        $result = '';
                        foreach ($updatedItemsArray as $item) {
                            $result = $item['category_variant']->name.': '.$item['value'].' | '.$result;
                        }

                        return rtrim($result, ' | '); // Remove trailing separator
                    }),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->headerActions([
                // Add header actions here if needed
            ])
            ->actions([
                // Add row actions here if needed
            ])
            ->bulkActions([
                // Add bulk actions here if needed
            ]);
    }
}
