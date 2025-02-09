<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'solar-ticket-sale-line-duotone';

    protected static ?string $navigationIconActive = 'solar-ticket-sale-bold-duotone';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $pluralLabel = 'Coupons';

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema(
                [
                    Section::make('Coupon Info')
                        ->description('Please enter the coupon info')
                        ->schema([
                            Fieldset::make('code')
                                ->schema([
                                    TextInput::make('code')
                                        ->required()
                                        ->maxLength(50)
                                        ->unique(ignoreRecord: true),
                                    TextInput::make('description')
                                        ->maxLength(255),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                            Select::make('discount_type')
                                ->options([
                                    'percentage' => 'Percentage',
                                    'fixed' => 'Fixed Amount',
                                ])
                                ->required(),
                            TextInput::make('discount_value')
                                ->required()
                                ->numeric(),
                            TextInput::make('max_discount_price')
                                ->required()
                                ->numeric(),
                            TextInput::make('max_uses')
                                ->numeric()
                                ->nullable(),
                            DatePicker::make('expires_at')
                                ->nullable(),
                            Checkbox::make('is_first_time_only')
                                ->default(false),
                        ])
                        ->columns(3),
                ]
            );
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('description')->limit(50),
                TextColumn::make('discount_type')->sortable(),
                TextColumn::make('discount_value')->sortable(),
                TextColumn::make('uses')->label('Used')->sortable(),
                TextColumn::make('max_uses')->label('Max Uses')->sortable(),
                TextColumn::make('expires_at')->date()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('expired')
                    ->query(fn ($query) => $query->where('expires_at', '<', now())),
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('expires_at', '>', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
