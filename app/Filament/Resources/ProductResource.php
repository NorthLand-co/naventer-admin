<?php

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\ProductType;
use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\AnswersRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\FaqRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Seo;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'solar-box-minimalistic-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-box-minimalistic-bold-duotone';

    protected static string|array $routeMiddleware = ['web'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('product')
                    ->columnSpanFull()
                    ->schema([
                        Tab::make('Product')
                            ->icon('solar-box-line-duotone')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->lazy()
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($get('name')))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->extraInputAttributes(['tabindex' => '-1']),
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('type')
                                                    ->disabled()
                                                    ->options(ProductType::options())
                                                    ->default('product'),
                                                TextInput::make('sku')
                                                    ->label('SKU'),
                                                TextInput::make('order')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->maxValue(255)
                                                    ->default(255),
                                            ]),
                                        Select::make('category_id')
                                            ->relationship('category', 'name')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (Forms\Set $set) {
                                                $set('variant_items', []); // Reset variant items when category changes
                                            })
                                            ->columnSpanFull(),
                                        Textarea::make('about')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Toggle::make('is_activated'),
                                        Toggle::make('is_trend'),
                                    ]),
                            ]),
                        Tab::make('Details')
                            ->icon('solar-info-circle-line-duotone')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('feature_image')
                                    ->required()
                                    ->collection('feature_image')
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->multiple()
                                    ->collection('gallery')
                                    ->columnSpanFull(),
                                TinyEditor::make('description'),
                                MarkdownEditor::make('details'),
                                SpatieTagsInput::make('tags')
                                    ->type('categories'),
                                Fieldset::make('customize')
                                    ->columns(2)
                                    ->schema([
                                        ColorPicker::make('color'),
                                        SpatieMediaLibraryFileUpload::make('background_image')
                                            ->collection('background_image'),
                                    ]),
                            ]),
                        Tab::make('Seo')
                            ->icon('solar-list-check-bold-duotone')
                            ->schema(Seo::getForm()),
                        Tab::make('Stock')
                            ->icon('solar-buildings-line-duotone')
                            ->schema([
                                Toggle::make('is_in_stock'),
                                Toggle::make('has_unlimited_stock'),
                                Toggle::make('has_max_cart')
                                    ->columnSpanFull()
                                    ->live(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('min_cart')
                                            ->hidden(fn (Forms\Get $get) => ! $get('has_max_cart'))
                                            ->numeric()
                                            ->nullable(),
                                        TextInput::make('max_cart')
                                            ->hidden(fn (Forms\Get $get) => ! $get('has_max_cart'))
                                            ->numeric()
                                            ->nullable(),
                                    ]),
                                Toggle::make('has_stock_alert')
                                    ->columnSpanFull()
                                    ->live(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('min_stock_alert')
                                            ->hidden(fn (Forms\Get $get) => ! $get('has_stock_alert'))
                                            ->numeric()
                                            ->nullable(),
                                        TextInput::make('max_stock_alert')
                                            ->hidden(fn (Forms\Get $get) => ! $get('has_stock_alert'))
                                            ->numeric()
                                            ->nullable(),
                                    ]),
                            ]),
                        Tab::make('Price')
                            ->icon('solar-tag-price-line-duotone')
                            ->schema([
                                Fieldset::make('Price Options')
                                    ->schema([
                                        Toggle::make('has_multi_price')
                                            ->afterStateHydrated(fn (?string $state, Forms\Set $set) => $set('max_items', $state ? 10 : 1))
                                            ->reactive(),
                                        Toggle::make('has_options')
                                            ->hidden(fn (Forms\Get $get) => $get('category_id') === null)
                                            ->reactive(),
                                        Hidden::make('max_items')
                                            ->default(fn (Forms\Get $get) => $get('has_multi_price') ? 10 : 1)
                                            ->reactive(),
                                    ]),
                                Repeater::make('price')
                                    ->maxItems(fn (Forms\Get $get) => $get('max_items') ?? 1)
                                    ->relationship('prices')
                                    ->columns(6)
                                    ->collapsed()
                                    ->cloneable()
                                    ->reorderable(true)
                                    ->reorderableWithButtons()
                                    ->schema([
                                        Grid::make()
                                            ->columns(6)
                                            ->schema(ProductPrice::getForm()),
                                        Repeater::make('special_prices')
                                            ->columns(6)
                                            ->columnSpanFull()
                                            ->relationship('specialPrices')
                                            ->schema([
                                                Select::make('special_prices_group_id')
                                                    ->relationship('group', 'name')
                                                    ->columnSpan(3)
                                                    ->required(),
                                                Grid::make()
                                                    ->columns(6)
                                                    ->relationship('price')
                                                    ->schema(ProductPrice::getForm()),
                                            ]),
                                        Repeater::make('variants_list')
                                            ->label('Variants List')
                                            ->relationship('variants')
                                            ->collapsible()
                                            ->visible(fn ($livewire) => $livewire->data['has_options'])
                                            ->columnSpanFull()
                                            ->schema([
                                                SpatieMediaLibraryFileUpload::make('thumb')
                                                    ->image()
                                                    ->responsiveImages()
                                                    ->collection('thumb')
                                                    ->columnSpanFull(),
                                                Grid::make()
                                                    ->schema([
                                                        TextInput::make('weight')
                                                            ->numeric()
                                                            ->prefix('گرم')
                                                            ->default(0),
                                                        TextInput::make('sku'),
                                                        TextInput::make('stock')
                                                            ->numeric()
                                                            ->minValue(0),
                                                    ]),
                                                Repeater::make('variant_items')
                                                    ->relationship('items')
                                                    ->label('Variant items')
                                                    ->columnSpanFull()
                                                    ->cloneable()
                                                    ->columns(2)
                                                    ->schema([
                                                        Select::make('category_variant_id')
                                                            ->label('Variant Name')
                                                            ->options(fn ($livewire) => Category::where('id', $livewire->data['category_id'])->first()?->variants()->pluck('name', 'id'))
                                                            ->required(),
                                                        TextInput::make('value')
                                                            ->required(),
                                                    ]),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('type')
                    ->badge(),
                ToggleColumn::make('is_in_stock'),
                ToggleColumn::make('is_trend'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            AttributesRelationManager::class,
            FaqRelationManager::class,
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getApiTransformer()
    {
        return ProductTransformer::class;
    }
}
