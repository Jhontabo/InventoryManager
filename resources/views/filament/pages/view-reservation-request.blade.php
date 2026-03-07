<x-filament::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Detalles de la reserva #{{ $record->id }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Creado el {{ $record->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <x-filament::badge :color="match ($record->status) {
                'pending' => 'warning',
                'approved', 'accepted' => 'success',
                'rejected' => 'danger',
                default => 'secondary',
            }" class="text-sm">
                {{ match ($record->status) {
                    'pending' => 'Pendiente por aprobar',
                    'approved', 'accepted' => 'Aprobada',
                    'rejected' => 'Rechazada',
                    default => ucfirst($record->status),
                } }}
            </x-filament::badge>
        </div>

        <!-- Información del Proyecto y Académica -->
        <x-filament::card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-academic-cap class="w-5 h-5 inline mr-2" />
                Información del proyecto
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="font-medium">Tipo de proyecto:</p>
                    <p>{{ $record->project_type ?? 'No especificado' }}</p>
                </div>
                <div>
                    <p class="font-medium">Semestre:</p>
                    <p>{{ $record->semester ?? 'No especificado' }}</p>
                </div>
                <div>
                    <p class="font-medium">Laboratorio:</p>
                    <p>{{ $record->laboratory->name ?? 'No especificado' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <p class="font-medium">Solicitante:</p>
                    <p>
                        {{
                            $record->applicant_name
                                ?? trim(($record->name ?? '') . ' ' . ($record->last_name ?? ''))
                                ?? 'No especificado'
                        }}
                    </p>
                </div>
                <div>
                    <p class="font-medium">Investigador principal:</p>
                    <p>
                        {{ $record->research_name ?? 'No especificado' }}
                    </p>
                </div>
                <div>
                    <p class="font-medium">Asesor:</p>
                    <p>
                        {{ $record->advisor ?? 'No especificado' }}
                    </p>
                </div>
            </div>
            
<div class="mt-4">
    <p class="font-medium mb-1">Equipos, materiales e insumos solicitados:</p>
    @php
        $nombresProductos = [];

        if (!empty($record->products)) {
            // Convertir string separado por comas a array si es necesario
            $ids = is_array($record->products)
                ? $record->products
                : explode(',', $record->products);

            $nombresProductos = \App\Models\Product::whereIn('id', $ids)->pluck('name')->toArray();
        }
    @endphp

    @if (!empty($nombresProductos))
        <ul class="list-disc ml-6">
            @foreach ($nombresProductos as $nombre)
                <li>{{ $nombre }}</li>
            @endforeach
        </ul>
    @else
        <p>No se especificaron equipos, materiales ni insumos.</p>
    @endif
</div>
        </x-filament::card>

        <!-- Información del Laboratorio y Horario -->
        <x-filament::card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        <x-heroicon-o-building-office class="w-5 h-5 inline mr-2" />
                        Información del Laboratorio
                    </h3>
                    <div class="space-y-2">
                        <p>
                            <span class="font-medium">Nombre:</span>
                            {{ $record->laboratory->name ?? 'No especificado' }}
                        </p>
                        <p>
                            <span class="font-medium">Localización:</span>
                            {{ $record->laboratory->location ?? 'No especificado' }}
                        </p>
                        <p>
                            <span class="font-medium">Capacidad:</span>
                            {{ $record->laboratory->capacity ?? 'No especificado' }}
                        </p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        <x-heroicon-o-clock class="w-5 h-5 inline mr-2" />
                        Horario Reservado
                    </h3>
                    <div class="space-y-2">
                        <p>
                            <span class="font-medium">Fecha:</span>
                            {{ $record->schedule?->start_at?->format('d/m/Y') ?? 'No especificado' }}
                        </p>
                        <p>
                            <span class="font-medium">Hora:</span>
                            {{ $record->schedule?->start_at?->format('H:i') ?? 'No especificado' }} –
                            {{ $record->schedule?->end_at?->format('H:i') ?? 'No especificado' }}
                        </p>
                        <p>
                            <span class="font-medium">Duración:</span>
                            {{ $record->schedule?->start_at && $record->schedule?->end_at ? $record->schedule->start_at->diffInHours($record->schedule->end_at) . ' horas' : 'No especificado' }}
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::card>

        <!-- Información del solicitante -->
        <x-filament::card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-user class="w-5 h-5 inline mr-2" />
                Información del solicitante
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="font-medium">Nombre:</p>
                    <p>{{ $record->name }}</p>
                </div>
                <div>
                    <p class="font-medium">Apellido:</p>
                    <p>{{ $record->last_name }}</p>
                </div>
                <div>
                    <p class="font-medium">Correo electrónico:</p>
                    <p>{{ $record->email }}</p>
                </div>
            </div>
        </x-filament::card>

        <!-- Detalles adicionales, estado, y motivo de rechazo si aplica -->
        <x-filament::card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        <x-heroicon-o-document-text class="w-5 h-5 inline mr-2" />
                        Detalles adicionales
                    </h3>
                    <div class="space-y-2">
                        <p>
                            <span class="font-medium">Fecha de solicitud:</span>
                            {{ $record->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p>
                            <span class="font-medium">Última actualización:</span>
                            {{ $record->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                @if ($record->status === 'rejected' && $record->rejection_reason)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            <x-heroicon-o-x-circle class="w-5 h-5 inline mr-2 text-danger-500" />
                            Motivo de Rechazo
                        </h3>
                        <p class="bg-danger-50 dark:bg-danger-900/50 p-4 rounded-lg">
                            {{ $record->rejection_reason }}
                        </p>
                    </div>
                @endif
            </div>
        </x-filament::card>
    </div>
</x-filament::page>
