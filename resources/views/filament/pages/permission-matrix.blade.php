<x-filament-panels::page>
    <div class="space-y-4">
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Cobertura de permisos por rol</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Revisa rápidamente qué acciones están permitidas por módulo para cada rol.
            </p>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Módulo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Acción</th>
                            @foreach ($roles as $role)
                                <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-200">{{ $role }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2.5 text-slate-800 dark:text-slate-100">
                                    {{ \Illuminate\Support\Str::title($row['module']) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">
                                    {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $row['action'])) }}
                                </td>
                                @foreach ($roles as $role)
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
                                <td colspan="{{ 2 + count($roles) }}" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
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
