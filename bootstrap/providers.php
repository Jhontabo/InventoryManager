<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\DocentePanelProvider;
use App\Providers\Filament\EstudiantePanelProvider;
use App\Providers\Filament\LaboratoristaPanelProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    AdminPanelProvider::class,
    DocentePanelProvider::class,
    EstudiantePanelProvider::class,
    LaboratoristaPanelProvider::class,
];
