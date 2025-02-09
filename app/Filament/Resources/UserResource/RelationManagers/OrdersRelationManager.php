<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\UserOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->groups([
                'status',
            ])
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('status')->badge(),
                TextColumn::make('price_with_discount'),
                TextColumn::make('shipment_price'),
                TextColumn::make('coupon_price'),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (UserOrder $record): string => route('filament.admin.resources.user-orders.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
