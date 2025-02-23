<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\RecommendationQuestion;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = RecommendationQuestion::class;

    protected static ?string $label = 'Recommendation';

    protected static ?string $navigationIcon = 'solar-question-square-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-question-square-bold-duotone';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Title'),

                TextInput::make('icon')
                    ->label('Icon'),

                Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                TextInput::make('order')
                    ->numeric()
                    ->required()
                    ->default(255)
                    ->label('Order'),

                TextInput::make('weight')
                    ->numeric()
                    ->required()
                    ->default(1.0)
                    ->label('Weight'),

                Repeater::make('answers')
                    ->relationship()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Answer Title'),

                        TextInput::make('icon')
                            ->label('Answer Icon'),

                        TextInput::make('order')
                            ->numeric()
                            ->required()
                            ->default(255)
                            ->label('Answer Order'),

                        Textarea::make('description')
                            ->columnSpanFull()
                            ->label('Answer Description'),
                    ])
                    ->label('Answers')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('description')
                    ->limit(200),
                TextColumn::make('order'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
