<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\EquipmentDecommission;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ViewEquipmentHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.resources.product-resource.pages.view-equipment-history';

    public Product $record;

    protected function getTableQuery(): Builder
    {
        return EquipmentDecommission::query()
            ->where('product_id', $this->record->id)
            ->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('reason')
                ->label('Motivo')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'damaged' => 'Dañado',
                    'maintenance' => 'Mantenimiento',
                    'lost' => 'Perdido',
                    'obsolete' => 'Obsoleto',
                    'other' => 'Otro',
                }),
            Tables\Columns\TextColumn::make('damage_type')
                ->label('Tipo de daño')
                ->formatStateUsing(fn (?string $state): string => $state ? match ($state) {
                    'student' => 'Estudiante',
                    'usage' => 'Uso',
                    'manufacturing' => 'Fabricación',
                    'other' => 'Otro',
                } : 'N/A'),
            Tables\Columns\TextColumn::make('details')
                ->label('Detalles')
                ->limit(50),
            Tables\Columns\TextColumn::make('decommission_date')
                ->label('Fecha de baja')
                ->date(),
            Tables\Columns\TextColumn::make('expected_return_date')
                ->label('Fecha esperada de retorno')
                ->date(),
            Tables\Columns\TextColumn::make('registeredBy.name')
                ->label('Registrado por'),
            Tables\Columns\TextColumn::make('reversedBy.name')
                ->label('Reversado por')
                ->default('N/A'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver')
                ->url(ProductResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
