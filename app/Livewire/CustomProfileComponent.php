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
    return Section::make('Información de contacto')
      ->aside()
      ->description('Actualiza tu número telefónico y dirección de residencia.')
      ->schema([
        $this->getPhoneInput(),
        $this->getAddressInput(),
      ]);
  }

  protected function getPhoneInput(): TextInput
  {
    return TextInput::make('phone')
      ->label('Teléfono')
      ->placeholder('Ejemplo: +57 314 567 8900')
      ->required()
      ->maxLength(15)
      ->tel() // Agrega validación específica para teléfonos
      ->helperText('Ingresa tu número con código de país');
  }

  protected function getAddressInput(): TextInput
  {
    return TextInput::make('address')
      ->label('Dirección')
      ->placeholder('Ejemplo: Calle Principal #123, Ciudad')
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
      ->title('Información actualizada exitosamente')
      ->success()
      ->body('Tus datos de contacto han sido guardados correctamente.')
      ->send();
  }

  protected function sendErrorNotification(): void
  {
    Notification::make()
      ->title('Error al actualizar')
      ->danger()
      ->body('Ocurrió un error al intentar guardar tus datos. Por favor intenta nuevamente.')
      ->send();
  }

  public function render(): View
  {
    return view('livewire.custom-profile-component');
  }
}
