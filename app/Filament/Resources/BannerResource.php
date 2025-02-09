<?php

namespace App\Filament\Resources;

use App\Enums\BannerType;
use App\Enums\Status;
use App\Filament\Resources\BannerResource\Api\Transformers\BannerTransformer;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'solar-gallery-minimalistic-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-gallery-minimalistic-bold-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make([
                        TextInput::make('name')
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('slug'),
                        TextInput::make('url'),
                        TextInput::make('class'),
                        TextInput::make('alt'),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->image()
                            ->imageEditor()
                            ->responsiveImages()
                            ->collection('image'),
                        SpatieMediaLibraryFileUpload::make('responsive_image')
                            ->image()
                            ->imageEditor()
                            ->responsiveImages()
                            ->collection('responsive_image'),
                        MarkdownEditor::make('description')
                            ->columnSpanFull(),
                        DatePicker::make('started_at'),
                        DatePicker::make('ended_at'),
                    ])->columns(2),
                    Section::make([
                        Select::make('type')
                            ->options(BannerType::options())
                            ->required()
                            ->searchable(),
                        Select::make('target')
                            ->options([
                                '_self' => 'Self',
                                '_blank' => 'New page',
                                '_top' => 'Top',
                                '_parent' => 'Parent',
                            ]),
                        Select::make('status')
                            ->options(Status::class)
                            ->required(),
                        TextInput::make('order')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(1),
                    ])->grow(false),
                ])
                    ->from('md')
                    ->columnSpanFull(),
                // Textarea::make('styles')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('status')
                    ->badge(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'view' => Pages\ViewBanner::route('/{record}'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }

    public static function getApiTransformer()
    {
        return BannerTransformer::class;
    }
}
