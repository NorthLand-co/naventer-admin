<?php

namespace App\Models;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;

class ProductPrice extends Model implements HasMedia
{
    use HasFactory;
    use HasUuid;
    use InteractsWithMedia;

    protected $fillable = ['product_id', 'price', 'discounted_price', 'discounted_to', 'vat', 'max_cart'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function specialPrices(): HasMany
    {
        return $this->hasMany(SpecialPrices::class);
    }

    public static function getForm()
    {
        return [
            TextInput::make('price')
                ->required()
                ->numeric()
                ->nullable()
                ->columnSpan(2)
                ->prefix('ریال'),
            TextInput::make('vat')
                ->numeric()
                ->nullable()
                ->columnSpan(2)
                ->prefix('ریال')
                ->default(0),
            TextInput::make('max_cart')
                ->numeric()
                ->nullable()
                ->columnSpan(2)
                ->default(0),
            TextInput::make('discounted_price')
                ->numeric()
                ->live()
                ->nullable()
                ->columnSpan(3)
                ->prefix('ریال')
                ->default(0),
            DateTimePicker::make('discount_to')
                ->columnSpan(3)
                ->rule('after:now')
                ->hidden(fn (Forms\Get $get) => ! $get('discounted_price')),
            SpatieMediaLibraryFileUpload::make('thumb')
                ->image()
                ->responsiveImages()
                ->collection('thumb')
                ->columnSpanFull(),
        ];
    }
}
