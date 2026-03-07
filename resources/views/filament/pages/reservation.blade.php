<x-filament-panels::page>
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Elegir horario</h3>
        <select name="laboratories"
                onchange="location.href='?laboratory='+this.value"
                class="form-control border-gray-300 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
            <option value="">Seleccione un laboratorio</option>
            @foreach ($this->getLaboratories() as $id => $name)
                <option value="{{ $id }}" {{ request()->query('id') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>

    
</x-filament-panels::page>
