<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Spatie\Permission\PermissionRegistrar;

class PermissionMatrix extends Page
{
    use HasPanelRoleAccess;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Matriz de permisos';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $title = 'Matriz de Permisos';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.permission-matrix';

    public array $roles = [];

    public array $rows = [];

    public array $modules = [];

    public array $actions = [];

    public string $selectedRole = '';

    public string $selectedModule = '';

    public string $selectedAction = '';

    public string $search = '';

    public static function canAccess(): bool
    {
        return static::userHasAnyRole(['ADMIN']);
    }

    protected static function canView(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->refreshMatrix();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cloneRolePermissions')
                ->label('Clonar permisos')
                ->icon('heroicon-o-document-duplicate')
                ->color('primary')
                ->form([
                    Select::make('from_role')
                        ->label('Rol origen')
                        ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'name')->toArray())
                        ->searchable()
                        ->required(),
                    Select::make('to_role')
                        ->label('Rol destino')
                        ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'name')->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->copyPermissionsBetweenRoles($data['from_role'], $data['to_role']);
                })
                ->requiresConfirmation(),
        ];
    }

    public function refreshMatrix(): void
    {
        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name']);

        $permissions = Permission::query()
            ->orderBy('name')
            ->get(['name']);

        $this->roles = $roles->pluck('name')->values()->all();

        $rolePermissionSets = [];
        foreach ($roles as $role) {
            $rolePermissionSets[$role->name] = array_flip($role->permissions->pluck('name')->all());
        }

        $rows = [];
        foreach ($permissions as $permission) {
            [$module, $action] = $this->parsePermissionName($permission->name);
            $rowKey = $module.'|'.$action;

            if (! isset($rows[$rowKey])) {
                $rows[$rowKey] = [
                    'module' => $module,
                    'action' => $action,
                    'permission' => $permission->name,
                    'roles' => [],
                ];
            }

            foreach ($this->roles as $roleName) {
                $rows[$rowKey]['roles'][$roleName] = isset($rolePermissionSets[$roleName][$permission->name]);
            }
        }

        $actionOrder = [
            'view_any' => 1,
            'view' => 2,
            'create' => 3,
            'update' => 4,
            'delete' => 5,
            'delete_any' => 6,
            'restore' => 7,
            'restore_any' => 8,
            'force_delete' => 9,
            'force_delete_any' => 10,
            'replicate' => 11,
            'reorder' => 12,
        ];

        uasort($rows, function (array $a, array $b) use ($actionOrder): int {
            $moduleCompare = strcmp($a['module'], $b['module']);
            if ($moduleCompare !== 0) {
                return $moduleCompare;
            }

            $aOrder = $actionOrder[$a['action']] ?? 999;
            $bOrder = $actionOrder[$b['action']] ?? 999;

            return $aOrder <=> $bOrder;
        });

        $this->rows = array_values($rows);
        $this->modules = array_values(array_unique(array_column($this->rows, 'module')));
        $this->actions = array_values(array_unique(array_column($this->rows, 'action')));
    }

    public function resetFilters(): void
    {
        $this->selectedRole = '';
        $this->selectedModule = '';
        $this->selectedAction = '';
        $this->search = '';
    }

    public function copyPermissionsBetweenRoles(string $fromRole, string $toRole): void
    {
        if ($fromRole === $toRole) {
            Notification::make()
                ->title('El rol origen y destino no pueden ser iguales.')
                ->danger()
                ->send();

            return;
        }

        $sourceRole = Role::query()->where('name', $fromRole)->first();
        $targetRole = Role::query()->where('name', $toRole)->first();

        if (! $sourceRole || ! $targetRole) {
            Notification::make()
                ->title('No se encontró uno de los roles seleccionados.')
                ->danger()
                ->send();

            return;
        }

        $targetRole->syncPermissions($sourceRole->permissions->pluck('name')->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->refreshMatrix();

        Notification::make()
            ->title("Permisos clonados de {$fromRole} a {$toRole}.")
            ->success()
            ->send();
    }

    public function getDisplayedRolesProperty(): array
    {
        if ($this->selectedRole !== '' && in_array($this->selectedRole, $this->roles, true)) {
            return [$this->selectedRole];
        }

        return $this->roles;
    }

    public function getFilteredRowsProperty(): array
    {
        return array_values(array_filter($this->rows, function (array $row): bool {
            if ($this->selectedModule !== '' && $row['module'] !== $this->selectedModule) {
                return false;
            }

            if ($this->selectedAction !== '' && $row['action'] !== $this->selectedAction) {
                return false;
            }

            if ($this->search !== '') {
                $haystack = mb_strtolower(
                    $row['module'].' '.$row['action'].' '.$row['permission']
                );

                return str_contains($haystack, mb_strtolower($this->search));
            }

            return true;
        }));
    }

    private function parsePermissionName(string $permission): array
    {
        $prefixes = [
            'force_delete_any_',
            'force_delete_',
            'delete_any_',
            'restore_any_',
            'view_any_',
            'create_',
            'update_',
            'delete_',
            'restore_',
            'replicate_',
            'reorder_',
            'view_',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($permission, $prefix)) {
                $module = substr($permission, strlen($prefix));
                $module = str_replace(['::', '_'], ' ', $module);

                return [$module, rtrim($prefix, '_')];
            }
        }

        $parts = explode(' ', $permission, 2);
        if (count($parts) === 2) {
            return [$parts[1], $parts[0]];
        }

        return [$permission, 'custom'];
    }
}
