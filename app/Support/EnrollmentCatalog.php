<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EnrollmentCatalog
{
    public static function types(): array
    {
        return config('enrollment_catalog.types', []);
    }

    public static function groups(): array
    {
        return config('enrollment_catalog.groups', []);
    }

    public static function type(string $key): ?array
    {
        return self::types()[$key] ?? null;
    }

    public static function modelClass(string $key): ?string
    {
        $class = self::type($key)['model'] ?? null;

        return is_string($class) && is_subclass_of($class, Model::class) ? $class : null;
    }

    public static function permissionPrefix(string $key): ?string
    {
        return self::type($key)['permission'] ?? null;
    }

    public static function groupedTypes(): array
    {
        $grouped = [];

        foreach (self::groups() as $groupKey => $groupLabel) {
            $grouped[$groupKey] = [
                'label' => $groupLabel,
                'types' => [],
            ];
        }

        foreach (self::types() as $typeKey => $config) {
            $group = $config['group'] ?? 'programs';
            $grouped[$group]['types'][$typeKey] = $config + ['key' => $typeKey];
        }

        return $grouped;
    }

    public static function defaults(string $type): array
    {
        return config("enrollment_catalog.defaults.{$type}", []);
    }

    public static function displayValue(object $row, array $config): string
    {
        $field = $config['field'] ?? 'name';

        return (string) ($row->{$field} ?? '');
    }

    public static function validatePayload(string $type, array $data): array
    {
        $config = self::type($type);
        abort_unless($config, 404);

        $field = $config['field'] ?? 'name';
        $rules = [
            $field => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
        ];

        if (($config['input'] ?? '') === 'date') {
            $rules[$field] = ['required', 'date'];
        }

        return validator($data, $rules)->validate();
    }

    public static function userCan(string $action, string $type): bool
    {
        $prefix = self::permissionPrefix($type);
        $user = auth('admin')->user();

        if (! $prefix || ! $user) {
            return false;
        }

        return match ($action) {
            'view' => $user->can("view {$prefix}"),
            'create' => $user->can("create {$prefix}"),
            'edit' => $user->can("edit {$prefix}"),
            'delete' => $user->can("delete {$prefix}"),
            default => false,
        };
    }
}
