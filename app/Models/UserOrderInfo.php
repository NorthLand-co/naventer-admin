<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserOrderInfo extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['user_order_id', 'type', 'details', 'description'];

    protected $casts = [
        'details' => 'array',
        'type' => OrderStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UserOrder::class);
    }

    // model form
    public static function getForm(UserOrder $record)
    {
        return [
            Hidden::make('user_order_id'),
            Hidden::make('type'),

            Repeater::make('details')
                ->label('Details')
                ->schema([
                    TextInput::make('key')->default(function ($record): string {
                        switch ($record->status) {
                            case OrderStatus::PROCESSING:
                                return 'tracking_number';

                            default:
                                return '';
                        }
                    })->required(),
                    TextInput::make('value')->required(),
                ])
                ->columns(2),

            Textarea::make('description')
                ->label('Description')
                ->rows(5)
                ->placeholder('Enter a brief description here...')
                ->maxLength(255),

            SpatieMediaLibraryFileUpload::make('gallery')
                ->model(UserOrderInfo::class)
                ->multiple()
                ->collection('gallery'),
        ];
    }
}
