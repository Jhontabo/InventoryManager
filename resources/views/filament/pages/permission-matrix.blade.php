<x-filament-panels::page>
    <div class="space-y-4">
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Cobertura de permisos por rol</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Revisa rápidamente qué acciones están permitidas por módulo para cada rol.
            </p>

            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Rol</label>
                    <select wire:model.live="selectedRole" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Todos</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Módulo</label>
                    <select wire:model.live="selectedModule" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Todos</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}">{{ \Illuminate\Support\Str::title($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Acción</label>
                    <select wire:model.live="selectedAction" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Todas</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">Buscar</label>
                    <input
                        wire:model.live.debounce.250ms="search"
                        type="text"
                        placeholder="permiso o módulo"
                        class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900"
                    />
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ count($this->filteredRows) }} filas visibles
                </p>
                <x-filament::button color="gray" size="sm" wire:click="resetFilters">
                    Limpiar filtros
                </x-filament::button>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Módulo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Acción</th>
                            @foreach ($this->displayedRoles as $role)
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-200">{{ $role }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($this->filteredRows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2.5 text-slate-800 dark:text-slate-100">
                                    {{ \Illuminate\Support\Str::title($row['module']) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">
                                    {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $row['action'])) }}
                                </td>
                                @foreach ($this->displayedRoles as $role)
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($row['roles'][$role] ?? false)
                                            <x-filament::icon
                                                icon="heroicon-o-check-circle"
                                                class="mx-auto h-5 w-5 text-emerald-600 dark:text-emerald-400"
                                            />
                                        @else
                                            <x-filament::icon
                                                icon="heroicon-o-x-circle"
                                                class="mx-auto h-5 w-5 text-slate-400 dark:text-slate-500"
                                            />
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + count($this->displayedRoles) }}" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                    No hay permisos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
