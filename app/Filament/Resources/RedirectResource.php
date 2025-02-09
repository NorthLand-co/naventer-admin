<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Api\Transformers\RedirectTransformer;
use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'solar-route-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-route-bold-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('source_url')
                            ->required(),
                        TextInput::make('destination_url')
                            ->required(),
                        Select::make('redirect_code')
                            ->options([
                                '301' => 'Moved Permanently - 301',
                                '307' => 'Temporary Redirect - 307',
                                '308' => 'Permanent Redirect - 308',
                                '302' => 'Found - 302',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_url'),
                TextColumn::make('destination_url'),
                TextColumn::make('redirect_code'),
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
            'index' => Pages\ListRedirects::route('/'),
        ];
    }

    public static function getApiTransformer()
    {
        return RedirectTransformer::class;
    }
}
