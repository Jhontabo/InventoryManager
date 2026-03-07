<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Models\Permission;
use App\Models\Role;
use Filament\Pages\Page;

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
