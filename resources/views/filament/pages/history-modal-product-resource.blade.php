<div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
    @if($history->isEmpty())
        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            <h3 class="mt-2 text-sm font-medium">No hay registros de historial</h3>
            <p class="mt-1 text-sm">Este equipo no tiene eventos registrados en su historial.</p>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Motivo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observaciones</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Registrado por</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Semestre</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estudiante responsable</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Documento</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Programa académico</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                @foreach($history as $record)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            {{ match($record->reason) {
                                'damaged' => 'Dañado',
                                'maintenance' => 'Mantenimiento',
                                'lost' => 'Perdido',
                                'obsolete' => 'Obsoleto',
                                'other' => 'Otro',
                                default => $record->reason
                            } }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-200">
                            {{ $record->observations ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            {{ $record->decommission_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            {{ $record->registeredBy->name }} {{ $record->registeredBy->last_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            {{ $record->semester ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            @if($record->responsibleUser)
                                {{ $record->responsibleUser->name }} {{ $record->responsibleUser->last_name }}
                            @else
                                No registrado
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            @if($record->responsibleUser)
                                {{ $record->responsibleUser->document_number ?? 'Sin documento' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-200">
                            {{ $record->academic_program ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
