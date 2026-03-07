<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\AvailableProductResource\Pages;
use App\Models\AvailableProduct;
use App\Services\LoanService;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

class AvailableProductResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = AvailableProduct::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string | \UnitEnum | null $navigationGroup = 'Préstamos';

    protected static ?string $navigationLabel = 'Solicitar préstamo';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralLabel = 'Productos para préstamos';

    public static function getEloquentQuery(): Builder
    {
        // Solo productos aptos para préstamo con datos mínimos necesarios.
        return parent::getEloquentQuery()
            ->with(['laboratory:id,name'])
            ->select([
                'id',
                'name',
                'image',
                'laboratory_id',
                'available_quantity',
                'product_type',
                'available_for_loan',
                'status',
            ])
            ->where('available_for_loan', true)
            ->whereIn('status', ['new', 'used']);
    }

    public static function canViewAny(): bool
    {
        return static::userHasNoneRole(['LABORATORISTA', 'COORDINADOR']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->size(60)
                    ->circular()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->laboratory->name ?? 'Sin laboratorio', 'below')
                    ->color('primary'),

                TextColumn::make('available_quantity')
                    ->label('Disponibles')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->available_quantity <= 0 => 'danger',
                        $record->available_quantity < 5 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => "{$state} uds"),

                TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'equipment' => 'info',
                        'supply' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'equipment' => 'Equipo',
                        'supply' => 'Insumo',
                        default => ucfirst($state),
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('laboratory_id')
                    ->label('Laboratorio')
                    ->options(fn () => \App\Models\Laboratory::all()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn ($record) => view('filament.pages.view-AvailableProduct', ['product' => $record])),

                Actions\Action::make('requestLoan')
                    ->label('Solicitar Préstamo')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->button()
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar solicitud')
                    ->modalDescription(fn (AvailableProduct $record) => "¿Confirma la solicitud del producto '{$record->name}'?\n\n".
                        "Cantidad disponible: {$record->available_quantity} unidades"
                    )
                    ->action(function (AvailableProduct $record) {
                        try {
                            app(LoanService::class)->request(auth()->user(), (int) $record->id);

                            Notification::make()
                                ->title('Solicitud enviada')
                                ->success()
                                ->body("Tu solicitud para {$record->name} está siendo procesada. Te notificaremos cuando sea aprobada.")
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('No fue posible crear la solicitud')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->emptyState(view('filament.pages.empty-state-products'))
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAvailableProducts::route('/'),
        ];
    }
}
