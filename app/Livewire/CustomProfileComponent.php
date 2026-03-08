<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;

class CustomProfileComponent extends Component implements HasForms
{
  use Forms\Concerns\InteractsWithForms;
  use HasSort;

  public ?array $data = [];
  protected static int $sort = 0;

  public function mount(): void
  {
    $this->fillFormWithUserData();
  }

  protected function fillFormWithUserData(): void
  {
    $user = Auth::user();

    $this->form->fill([
      'phone' => $user->phone,
      'address' => $user->address,
    ]);
  }

  public function form(Schema $schema): Schema
  {
    return $schema
      ->schema([
        $this->getContactInformationSection(),
      ])
      ->statePath('data');
  }

  protected function getContactInformationSection(): Section
  {
    return Section::make(__('panel.profile.contact_section'))
      ->aside()
      ->description(__('panel.profile.contact_description'))
      ->schema([
        $this->getPhoneInput(),
        $this->getAddressInput(),
      ]);
  }

  protected function getPhoneInput(): TextInput
  {
    return TextInput::make('phone')
      ->label(__('panel.profile.phone'))
      ->placeholder(__('panel.profile.phone_placeholder'))
      ->required()
      ->maxLength(15)
      ->tel() // Agrega validación específica para teléfonos
      ->helperText(__('panel.profile.phone_help'));
  }

  protected function getAddressInput(): TextInput
  {
    return TextInput::make('address')
      ->label(__('panel.profile.address'))
      ->placeholder(__('panel.profile.address_placeholder'))
      ->required()
      ->maxLength(255)
      ->columnSpanFull();
  }

  public function save(): void
  {
    try {
      $data = $this->form->getState();

      /** @var User $user */
      $user = Auth::user();

      $user->update($data);

      $this->sendSuccessNotification();
    } catch (\Exception $e) {
      $this->sendErrorNotification();
    }
  }

  protected function sendSuccessNotification(): void
  {
    Notification::make()
      ->title(__('panel.profile.saved_title'))
      ->success()
      ->body(__('panel.profile.saved_body'))
      ->send();
  }

  protected function sendErrorNotification(): void
  {
    Notification::make()
      ->title(__('panel.profile.error_title'))
      ->danger()
      ->body(__('panel.profile.error_body'))
      ->send();
  }

  public function render(): View
  {
    return view('livewire.custom-profile-component');
  }
}
