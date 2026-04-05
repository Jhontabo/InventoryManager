<?php

namespace App\Filament\Resources\LaboratoryResource\Pages;

use App\Filament\Resources\LaboratoryResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateLaboratory extends CreateRecord
{
    protected static string $resource = LaboratoryResource::class;

    /**
     * @var array<int>
     */
    protected array $productIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->productIds = $data['product_ids'] ?? [];
        unset($data['product_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->productIds === []) {
            return;
        }

        Product::whereIn('id', $this->productIds)->update(['laboratory_id' => $this->record->id]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
