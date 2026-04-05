<?php

namespace App\Filament\Resources\AcademicProgramResource\Pages;

use App\Filament\Resources\AcademicProgramResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAcademicProgram extends CreateRecord
{
    protected static string $resource = AcademicProgramResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
