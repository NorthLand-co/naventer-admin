<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallResource\Pages;
use App\Models\CRM\Call;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CallResource extends Resource
{
    protected static ?string $model = Call::class;

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'solar-phone-calling-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-phone-calling-bold-duotone';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(Call::getFormTableColumn())
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
            'index' => Pages\ListCalls::route('/'),
            'create' => Pages\CreateCall::route('/create'),
            'edit' => Pages\EditCall::route('/{record}/edit'),
        ];
    }
}
