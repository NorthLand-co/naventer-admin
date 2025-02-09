<?php

namespace App\Filament\Resources;

use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use App\Enums\BlogPostType;
use App\Enums\Status;
use App\Filament\Resources\BlogPostResource\Api\Transformers\BlogPostTransformer;
use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use App\Models\Seo;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'solar-pen-new-square-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-pen-new-square-bold-duotone';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $navigationLabel = 'Posts';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('post')
                    ->columnSpanFull()
                    ->schema([
                        Tab::make('title')
                            ->label(trans('general.title'))
                            ->icon('solar-text-bold')
                            ->columns(4)
                            ->schema([
                                TextInput::make('title')
                                    ->label(trans('general.title'))
                                    ->required()
                                    ->columnSpan(2)
                                    ->lazy()
                                    ->maxLength(191)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($get('title')))),
                                TextInput::make('slug')
                                    ->label(trans('general.slug'))
                                    ->required()
                                    ->columnSpan(2)
                                    ->maxLength(191)
                                    ->extraInputAttributes(['tabindex' => '-1']),
                                Select::make('status')
                                    ->label(trans('general.status'))
                                    ->options(Status::options())
                                    ->required()
                                    ->default(0),
                                Select::make('post_type')
                                    ->label(trans('general.post_type'))
                                    ->options(BlogPostType::options())
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label(trans('general.is_featured')),
                                Toggle::make('allow_comments')
                                    ->label(trans('general.allow_comments'))->default(1),
                            ]),
                        Tab::make('content')
                            ->label(trans('general.content'))
                            ->icon('solar-pen-new-square-line-duotone')
                            ->columns(1)
                            ->schema([
                                MarkdownEditor::make('excerpt')
                                    ->columnSpanFull(),
                                TinyEditor::make('content')
                                    ->required()
                                    ->direction('auto')
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('thumb')
                                    ->image()
                                    ->imageEditor()
                                    ->responsiveImages()
                                    ->collection('thumb'),
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->image()
                                    ->multiple()
                                    ->imageEditor()
                                    ->responsiveImages()
                                    ->collection('gallery'),
                            ]),
                        Tab::make('seo')
                            ->label(trans('general.seo'))
                            ->icon('solar-list-check-bold-duotone')
                            ->schema(Seo::getForm()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumb')->collection('thumb'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('post_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('view_count')
                    ->label('View')
                    ->numeric(),
                TextColumn::make('like_count')
                    ->label('Like')
                    ->numeric(),
                TextColumn::make('comment_count')
                    ->label('Comment')
                    ->numeric(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                CommentsAction::make(),
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getApiTransformer()
    {
        return BlogPostTransformer::class;
    }
}
