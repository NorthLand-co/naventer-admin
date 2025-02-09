<?php

namespace App\Filament\Exports;

use App\Models\UserOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserOrderExporter extends Exporter
{
    protected static ?string $model = UserOrder::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('order_number'),
            ExportColumn::make('status')->formatStateUsing(fn (object $state): string => __($state->name)),
            ExportColumn::make('user.name'),
            ExportColumn::make('user_address_id'),
            ExportColumn::make('price'),
            ExportColumn::make('price_with_discount'),
            ExportColumn::make('shipping_variant_id'),
            ExportColumn::make('shipment_price'),
            ExportColumn::make('coupon'),
            ExportColumn::make('coupon_price'),
            ExportColumn::make('description'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user order export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
