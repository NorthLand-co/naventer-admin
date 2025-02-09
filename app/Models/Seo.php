<?php

namespace App\Models;

use App\Enums\Socials;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Seo extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = ['seoable_id', 'seoable_type', 'title', 'description', 'social', 'keywords', 'robots', 'canonical_url', 'og_title', 'og_description', 'og_image'];

    protected $casts = [
        'social' => 'json',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getForm()
    {
        return [
            Grid::make(2)
                ->relationship('seo')
                ->schema([
                    TextInput::make('title')
                        ->required(),
                    TextInput::make('keywords'),
                    Textarea::make('description')
                        ->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('image')
                        ->image()
                        ->responsiveImages()
                        ->collection('image')
                        ->columnSpanFull(),
                    TextInput::make('robots'),
                    TextInput::make('canonical_url'),
                    Repeater::make('social')
                        ->defaultItems(0)
                        ->columnSpanFull()
                        ->schema([
                            Select::make('name')
                                ->options(Socials::options())
                                ->required(),
                            TextInput::make('link'),
                            TextInput::make('title'),
                            Textarea::make('description')->rows(2),
                        ]),
                    TextInput::make('og_title'),
                    TextInput::make('og_description'),
                    SpatieMediaLibraryFileUpload::make('og_image')
                        ->image()
                        ->responsiveImages()
                        ->collection('og_image')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
