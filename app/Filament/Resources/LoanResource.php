<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LoanResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Loan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.loans');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.loan.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.loan.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.loan.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product:id,name,image'])
            ->select([
                'id',
                'product_id',
                'user_id',
                'status',
                'requested_at',
                'approved_at',
                'estimated_return_at',
                'actual_return_at',
            ])
            ->where('user_id', Auth::id());
    }

    public static function canViewAny(): bool
    {
        return static::userHasNoneRole(['COORDINADOR']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Imagen')
                    ->size(50),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Loan::STATUS_PENDING => 'warning',
                        Loan::STATUS_APPROVED => 'success',
                        Loan::STATUS_REJECTED => 'danger',
                        Loan::STATUS_RETURNED => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Loan::STATUS_PENDING => 'Pendiente',
                        Loan::STATUS_APPROVED => 'Aprobado',
                        Loan::STATUS_REJECTED => 'Rechazado',
                        Loan::STATUS_RETURNED => 'Devuelto',
                        default => ucfirst($state),
                    }),

                TextColumn::make('requested_at')
                    ->label('Fecha de solicitud')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Fecha de aprobación')
                    ->dateTime('d M Y H:i')
                    ->placeholder('No aprobado')
                    ->sortable(),

                TextColumn::make('estimated_return_at')
                    ->label('Fecha estimada de devolución')
                    ->dateTime('d M Y')
                    ->placeholder('No asignado'),

                TextColumn::make('actual_return_at')
                    ->label('Devuelto')
                    ->dateTime('d M Y H:i')
                    ->placeholder('No devuelto'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Loan::STATUS_PENDING => 'Pendiente',
                        Loan::STATUS_APPROVED => 'Aprobado',
                        Loan::STATUS_REJECTED => 'Rechazado',
                        Loan::STATUS_RETURNED => 'Devuelto',
                    ])
                    ->label('Estado del préstamo'),
            ])
            ->defaultSort('requested_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->striped()
            ->emptyStateHeading('Aún no tienes préstamos registrados')
            ->emptyStateDescription('Aquí verás el estado de tus solicitudes y devoluciones.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
        ];
    }
}
