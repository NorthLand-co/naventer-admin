<?php

namespace App\Models\CRM;

use App\Models\FieldOption;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Call extends Model
{
    protected $fillable = ['callable_id', 'callable_type', 'date', 'type', 'notes', 'user_id'];

    public function callable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeOption(): BelongsTo
    {
        return $this->belongsTo(FieldOption::class, 'type', 'id');
    }

    public static function getForm(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    Select::make('type')
                        ->label('Type')
                        ->options(function () {
                            return FieldOption::forField(Call::class, 'type')
                                ->pluck('value', 'id');
                        })
                        ->createOptionForm([
                            Hidden::make('model')
                                ->default(Call::class)
                                ->required(),
                            Hidden::make('field')
                                ->default('type')
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
                    DateTimePicker::make('date')
                        ->jalali(),
                    Textarea::make('notes')
                        ->columnSpanFull()
                        ->rows(8),
                    Hidden::make('user_id')
                        ->default(Auth::id())
                        ->required(),
                ]),
        ];
    }

    public static function getFormTableColumn(): array
    {
        return [
            TextColumn::make('typeOption.value')
                ->label('Type'),
            TextColumn::make('date')
                ->jalaliDateTime('Y/m/d - H:i'),
            TextColumn::make('notes')
                ->lineClamp(3),
            TextColumn::make('user.name'),
            TextColumn::make('callable')
                ->formatStateUsing(function ($record) {
                    $callable = $record->callable;

                    if (! $callable) {
                        return '-';
                    }

                    $resourceMap = [
                        'App\Models\UserOrder' => 'user-orders',
                        'App\Models\CRM\Lead' => 'leads',
                    ];

                    $resource = $resourceMap[get_class($callable)] ?? null;
                    if ($resource) {
                        $url = route("filament.admin.resources.{$resource}.view", ['record' => $callable->id]);

                        return "<a href='{$url}' class='text-sm text-center underline'>For</a>";
                    }

                    return '-';
                })
                ->icon('solar-eye-line-duotone')
                ->color('primary')
                ->html(),
        ];
    }
}
