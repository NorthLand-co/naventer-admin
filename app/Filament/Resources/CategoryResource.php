<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Api\Transformers\CategoryTransformer;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\CategoryAttributesRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\VariantsRelationManager;
use App\Models\Category;
use App\Models\Seo;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'solar-widget-2-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-widget-2-bold-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Details')
                            ->icon('solar-info-circle-line-duotone')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->lazy()
                                            ->maxLength(255)
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($get('name')))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->extraInputAttributes(['tabindex' => '-1']),
                                        TextInput::make('icon'),
                                        TextInput::make('order')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(255)
                                            ->default(255),
                                    ]),
                                SpatieMediaLibraryFileUpload::make('thump')
                                    ->image()
                                    ->responsiveImages()
                                    ->collection('thump'),
                                Select::make('parent_category_id')
                                    ->label('Parent Category')
                                    ->placeholder('Select parent category')
                                    ->relationship(name: 'parent', titleAttribute: 'name')
                                    ->nullable()
                                    ->searchable(),
                                SpatieTagsInput::make('tags')
                                    ->type('categories'),
                            ]),
                        Tab::make('Seo')
                            ->icon('solar-list-check-bold-duotone')
                            ->schema(Seo::getForm()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('parent.name'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CategoryAttributesRelationManager::class,
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getApiTransformer()
    {
        return CategoryTransformer::class;
    }
}
