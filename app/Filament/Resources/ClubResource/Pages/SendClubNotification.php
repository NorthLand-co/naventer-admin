<?php

namespace App\Filament\Resources\ClubResource\Pages;

use App\Filament\Resources\ClubResource;
use App\Models\Club;
use App\Services\ClubService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;

class SendClubNotification extends Page implements HasForms
{
    use InteractsWithForms;

    public $receptors;

    public $message;

    protected static string $resource = ClubResource::class;

    protected static string $view = 'filament.resources.club-resource.pages.send-club-notification';

    public function mount(): void
    {
        $this->form->fill([]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('send')
                ->label('Send')
                ->action('submitForm')
                ->color('primary'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('receptors')
                ->label('Receptors')
                ->options(Club::all()->pluck('name', 'id'))
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => Club::where('phone', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray())
                ->multiple(),
            Textarea::make('message')
                ->label('Message')
                ->rows(10)
                ->required(),
        ];
    }

    public function submitForm()
    {

        $data = $this->form->getState();

        $clubService = new ClubService;
        $clubService->notify($data);

        $this->form->fill([]);
        // $this->form->reset();
    }
}
