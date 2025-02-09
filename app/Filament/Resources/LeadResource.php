<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Contact;
use App\Models\CRM\Call;
use App\Models\CRM\Lead;
use App\Models\FieldOption;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'solar-cardholder-line-duotone';

    protected static ?string $activeNavigationIcon = 'solar-cardholder-bold-duotone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required(),
                Select::make('contact_id')
                    ->label('Contact')
                    ->searchable()
                    ->options(function () {
                        return Contact::all()
                            ->mapWithKeys(function ($contact) {
                                return [$contact->id => "{$contact->name} {$contact->family} - {$contact->phone}"];
                            });
                    })
                    ->createOptionForm([
                        TextInput::make('name'),
                        TextInput::make('family')
                            ->required(),
                        TextInput::make('email'),
                        TextInput::make('phone'),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return Contact::create([
                            'name' => $data['name'],
                            'family' => $data['family'],
                            'email' => $data['email'],
                            'phone' => $data['phone'],
                        ])->id;
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('contact_id', $state);
                        }
                    })
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(15)
                    ->label('Notes'),
                Select::make('source')
                    ->label('Source')
                    ->options(function () {
                        return FieldOption::forField(Lead::class, 'source')
                            ->pluck('value', 'id');
                    })
                    ->createOptionForm([
                        Hidden::make('model')
                            ->default(Lead::class)
                            ->required(),
                        Hidden::make('field')
                            ->default('source')
                            ->required(),
                        TextInput::make('value')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return FieldOption::create([
                            'model' => $data['model'],
                            'field' => $data['field'],
                            'value' => $data['value'],
                        ])->id;
                    })
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options(function () {
                        return FieldOption::forField(Lead::class, 'status')
                            ->pluck('value', 'id');
                    })
                    ->createOptionForm([
                        Hidden::make('model')
                            ->default(Lead::class)
                            ->required(),
                        Hidden::make('field')
                            ->default('status')
                            ->required(),
                        TextInput::make('value')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return FieldOption::create([
                            'model' => $data['model'],
                            'field' => $data['field'],
                            'value' => $data['value'],
                        ])->id;
                    })
                    ->required(),
                Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact')
                    ->label('Contact Name')
                    ->formatStateUsing(function ($record) {
                        return $record->contact->name.' '.$record->contact->family;
                    }),
                TextColumn::make('statusOption.value'),
                TextColumn::make('sourceOption.value'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('addCall')
                    ->label(fn (Lead $record): string => 'Add Call ('.$record->calls()->count().')')
                    ->icon('solar-call-medicine-line-duotone')
                    ->color('orange')
                    ->form(fn (Lead $record): array => Call::getForm($record))
                    ->action(function (array $data, Lead $record): void {
                        Arr::forget($data, ['callable_id', 'callable_type']);
                        $data['callable_id'] = $record->id;
                        $data['callable_type'] = Lead::class;
                        Call::create($data);
                    }),
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
            RelationManagers\CallsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id(); // Ensure the user_id is set

        return $data;
    }
}
