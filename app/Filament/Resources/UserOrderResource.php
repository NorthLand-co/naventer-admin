<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Exports\UserOrderExporter;
use App\Filament\Resources\UserOrderResource\Pages;
use App\Filament\Resources\UserOrderResource\RelationManagers\CallsRelationManager;
use App\Filament\Resources\UserOrderResource\RelationManagers\ItemsRelationManager;
use App\Models\CRM\Call;
use App\Models\UserOrder;
use App\Models\UserOrderInfo;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class UserOrderResource extends Resource
{
    protected static ?string $model = UserOrder::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'solar-cart-check-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-cart-check-bold-duotone';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'address', 'address.city', 'address.state', 'address.country', 'shipping.method', 'items']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Info')
                    ->description('here you can see details of an order')
                    ->schema([
                        Fieldset::make('Order Info')
                            ->columns(4)
                            ->schema([
                                TextInput::make('id'),
                                TextInput::make('order_number'),
                                TextInput::make('user_name')
                                    ->label('User')
                                    ->afterStateHydrated(fn ($state, $set, $record) => $set('user_name', $record->user?->name)),
                                Select::make('status')
                                    ->options(OrderStatus::class),
                            ]),
                        Fieldset::make('Address Info')
                            ->columns(2)
                            ->schema([
                                Textarea::make('user_address')->columnSpanFull()
                                    ->label('Address')
                                    ->afterStateHydrated(fn ($state, $set, $record) => $set('user_address', "{$record->address->country->name} - {$record->address->state->name} - {$record->address->city->name} - {$record->address->address}")),
                                TextInput::make('postal_code')
                                    ->label('Postal Code')
                                    ->afterStateHydrated(fn ($state, $set, $record) => $set('postal_code', $record->address->postal_code)),
                                TextInput::make('phone_number')
                                    ->label('Phone')
                                    ->afterStateHydrated(fn ($state, $set, $record) => $set('phone_number', $record->address->phone_number)),
                            ]),
                        Fieldset::make('Price Info')
                            ->columns(2)
                            ->schema([
                                TextInput::make('price'),
                                TextInput::make('price_with_discount'),
                            ]),
                        Fieldset::make('Shipping Info')
                            ->columns(2)
                            ->schema([
                                TextInput::make('shipping')
                                    ->label('Shipping')
                                    ->afterStateHydrated(fn ($state, $set, $record) => $set('shipping', "{$record->shipping->method->name} - {$record->shipping->name}")),
                                TextInput::make('shipment_price'),
                            ]),
                        Fieldset::make('Coupon Info')
                            ->columns(2)
                            ->schema([
                                TextInput::make('coupon'),
                                TextInput::make('coupon_price'),
                            ]),
                        Textarea::make('description')
                            ->label('Description'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number'),
                TextColumn::make('user.name')
                    ->url(fn (UserOrder $record): string => route('filament.admin.resources.users.view', $record->user))
                    ->openUrlInNewTab(),
                TextColumn::make('price_with_discount'),
                TextColumn::make('shipment_price'),
                TextColumn::make('coupon_price'),
                SelectColumn::make('status')
                    ->options(OrderStatus::class)
                    ->rules(['required'])
                    ->selectablePlaceholder(false)
                    ->visible(function () {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->hasRole('super_admin');
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::PAID->value),
                Filter::make('created_at')
                    ->form([
                        TextInput::make('order_number'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->where('order_number', 'LIKE', '%'.$data['order_number'].'%');
                    }),
                SelectFilter::make('include_cities')
                    ->relationship('address.city', 'name')
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('exclude_cities')
                    ->relationship('address.city', 'name')
                    ->searchable()
                    ->multiple()
                    ->query(function ($query, $state) {
                        // Extract IDs from $state
                        if (count($state['values']) === 0) {
                            return;
                        }
                        $cityIds = collect($state)->pluck('id')->toArray();

                        // Apply the whereNotIn filter
                        return $query->whereNotIn('id', $cityIds);

                    }),
            ])
            ->actions([
                Action::make('progress')
                    ->label('Progress')
                    ->icon('solar-menu-dots-outline')
                    ->color('primary')
                    ->fillForm(fn (UserOrder $record): array => [
                        'user_order_id' => $record->id,
                        'type' => $record->status,
                    ])
                    ->form(fn (UserOrder $record): array => UserOrderInfo::getForm($record))
                    ->action(function (array $data, UserOrder $record): void {
                        UserOrderInfo::updateOrCreate(
                            ['type' => $data['type'], 'user_order_id' => $record->id],
                            $data
                        );
                        $record->status = $record->status->next();
                        $record->save();
                    }),
                Action::make('addCall')
                    ->label(fn (UserOrder $record): string => 'Add Call ('.$record->calls()->count().')')
                    ->icon('solar-call-medicine-line-duotone')
                    ->color('orange')
                    ->form(fn (UserOrder $record): array => Call::getForm($record))
                    ->action(function (array $data, UserOrder $record): void {
                        Arr::forget($data, ['callable_id', 'callable_type']);
                        $data['callable_id'] = $record->id;
                        $data['callable_type'] = UserOrder::class;
                        Call::create($data);
                    }),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserOrderExporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            CallsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserOrders::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
