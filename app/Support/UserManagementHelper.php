<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementHelper
{
    /** Roles that cannot be deleted or renamed by tenant admins. */
    public const PROTECTED_ROLES = ['super-admin'];

    /** Permissions that may only be held by the protected account owner role. */
    public const ACCESS_CONTROL_PERMISSIONS = [
        'create role', 'edit role', 'view role', 'delete role',
        'create permission', 'edit permission', 'view permission', 'delete permission',
        'create user', 'edit user', 'view user', 'delete user',
    ];

    public static function canManageAccess(?Admin $admin = null): bool
    {
        $admin ??= auth('admin')->user();

        return (bool) ($admin?->isPlatformAdmin() || $admin?->hasRole('super-admin'));
    }

    /** @return array{users: int, roles: int, permissions: int} */
    public static function stats(): array
    {
        return [
            'users' => Admin::query()
                ->forCurrentOrganization()
                ->where('is_platform_admin', false)
                ->count(),
            'roles' => Role::query()->where('guard_name', 'admin')->count(),
            'permissions' => Permission::query()->where('guard_name', 'admin')->count(),
        ];
    }

    public static function isProtectedRole(Role|string $role): bool
    {
        $name = $role instanceof Role ? $role->name : $role;

        return in_array($name, self::PROTECTED_ROLES, true);
    }

    public static function usersCountForRole(Role $role): int
    {
        return Admin::query()
            ->forCurrentOrganization()
            ->where('is_platform_admin', false)
            ->role($role->name)
            ->count();
    }

    public static function formatRoleName(string $name): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    public static function formatPermissionName(string $name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    public static function permissionAction(string $name): string
    {
        $parts = explode(' ', strtolower(trim($name)), 2);

        return $parts[0] ?? 'other';
    }

    public static function resolvePermissionGroup(string $name): string
    {
        $lower = strtolower($name);

        $rules = [
            'Team & Access' => ['dashboard', ' role', ' permission', ' user'],
            'CRM' => ['lead', 'customer', 'project', 'quotation', 'invoice', 'crm ', 'form submission'],
            'Email Marketing' => ['inbox', 'email', 'campaign', 'template', 'mailbox', 'draft', 'compose', 'sent', 'starred'],
            'Integrations' => ['integration'],
            'Admissions Setup' => [
                'nationality', 'admission_date', 'gender', 'relation_ship', 'country',
                'terms_condition', ' school', ' year', 'language', 'subject', 'package',
                'course', 'admission_studetns',
            ],
            'Forms & Applications' => [
                'staff_application', ' job', ' apply', ' enquire', ' referral', ' subscribe',
            ],
            'Events' => ['open_event', 'event_item', 'meet_speaker', 'open_event_form'],
            'Website & Settings' => ['setting'],
        ];

        foreach ($rules as $group => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, trim($keyword))) {
                    return $group;
                }
            }
        }

        return 'Other';
    }

    /** @return array<string, Collection<int, Permission>> */
    public static function groupPermissions(Collection $permissions): array
    {
        $grouped = $permissions
            ->sortBy('name')
            ->groupBy(fn (Permission $permission) => self::resolvePermissionGroup($permission->name))
            ->all();

        uksort($grouped, function (string $a, string $b): int {
            if ($a === 'Other') {
                return 1;
            }
            if ($b === 'Other') {
                return -1;
            }

            return strcmp($a, $b);
        });

        return $grouped;
    }

    public static function groupIcon(string $group): string
    {
        return match ($group) {
            'Team & Access' => 'solar:users-group-two-rounded-linear',
            'CRM' => 'solar:chart-2-linear',
            'Email Marketing' => 'solar:letter-linear',
            'Integrations' => 'solar:plug-circle-linear',
            'Admissions Setup' => 'solar:square-academic-cap-linear',
            'Forms & Applications' => 'solar:document-add-linear',
            'Events' => 'solar:calendar-linear',
            'Website & Settings' => 'solar:monitor-smartphone-linear',
            default => 'solar:widget-5-linear',
        };
    }

    public static function actionBadgeClass(string $action): string
    {
        return match ($action) {
            'view' => 'um-action-badge--view',
            'create' => 'um-action-badge--create',
            'edit', 'update' => 'um-action-badge--edit',
            'delete' => 'um-action-badge--delete',
            'export', 'import' => 'um-action-badge--export',
            'send', 'compose' => 'um-action-badge--send',
            default => 'um-action-badge--other',
        };
    }

    public static function avatarGradient(string $seed): string
    {
        $palettes = [
            ['#0F274A', '#3d5a80'],
            ['#2563eb', '#60a5fa'],
            ['#7c3aed', '#a78bfa'],
            ['#0891b2', '#22d3ee'],
            ['#16a34a', '#4ade80'],
            ['#d97706', '#fbbf24'],
        ];

        $index = abs(crc32($seed)) % count($palettes);
        [$from, $to] = $palettes[$index];

        return "linear-gradient(135deg, {$from}, {$to})";
    }

    public static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr($parts[1], 0, 1));
        }

        return strtoupper(Str::substr($name, 0, 2));
    }

    /** @return array<int, array{value: string, label: string, hint: string}> */
    public static function permissionBuilderActions(): array
    {
        return [
            ['value' => 'view', 'label' => 'View', 'hint' => 'Browse and open records'],
            ['value' => 'create', 'label' => 'Create', 'hint' => 'Add new records'],
            ['value' => 'edit', 'label' => 'Edit', 'hint' => 'Change existing records'],
            ['value' => 'update', 'label' => 'Update', 'hint' => 'Modify existing records'],
            ['value' => 'delete', 'label' => 'Delete', 'hint' => 'Remove records permanently'],
            ['value' => 'export', 'label' => 'Export', 'hint' => 'Download data'],
            ['value' => 'import', 'label' => 'Import', 'hint' => 'Upload data in bulk'],
            ['value' => 'send', 'label' => 'Send', 'hint' => 'Email or deliver to customers'],
            ['value' => 'assign', 'label' => 'Assign', 'hint' => 'Allocate work to teammates'],
            ['value' => 'convert', 'label' => 'Convert', 'hint' => 'Move records through a workflow'],
            ['value' => 'record', 'label' => 'Record', 'hint' => 'Log payments or activity'],
            ['value' => 'manage', 'label' => 'Manage', 'hint' => 'Configure module settings'],
            ['value' => 'compose', 'label' => 'Compose', 'hint' => 'Write new messages'],
            ['value' => 'star', 'label' => 'Star', 'hint' => 'Mark items as important'],
        ];
    }

    /** @return array<int, array{key: string, label: string, icon: string, resources: array<int, array{value: string, label: string}>, allow_custom?: bool}> */
    public static function permissionBuilderModules(): array
    {
        return [
            [
                'key' => 'team',
                'label' => 'Team & Access',
                'icon' => 'solar:users-group-two-rounded-linear',
                'resources' => [
                    ['value' => 'dashboard', 'label' => 'Dashboard'],
                    ['value' => 'role', 'label' => 'Roles'],
                    ['value' => 'permission', 'label' => 'Permissions'],
                    ['value' => 'user', 'label' => 'Users'],
                ],
            ],
            [
                'key' => 'crm',
                'label' => 'CRM',
                'icon' => 'solar:chart-2-linear',
                'resources' => [
                    ['value' => 'leads', 'label' => 'Leads'],
                    ['value' => 'customers', 'label' => 'Customers'],
                    ['value' => 'projects', 'label' => 'Projects'],
                    ['value' => 'quotations', 'label' => 'Quotations'],
                    ['value' => 'invoices', 'label' => 'Invoices'],
                    ['value' => 'form submissions', 'label' => 'Form submissions'],
                    ['value' => 'crm documents', 'label' => 'CRM documents'],
                ],
            ],
            [
                'key' => 'email',
                'label' => 'Email Marketing',
                'icon' => 'solar:letter-linear',
                'resources' => [
                    ['value' => 'inbox', 'label' => 'Inbox'],
                    ['value' => 'sent emails', 'label' => 'Sent emails'],
                    ['value' => 'drafts', 'label' => 'Drafts'],
                    ['value' => 'starred', 'label' => 'Starred messages'],
                    ['value' => 'campaigns', 'label' => 'Campaigns'],
                    ['value' => 'templates', 'label' => 'Templates'],
                    ['value' => 'mailbox settings', 'label' => 'Mailbox settings'],
                ],
            ],
            [
                'key' => 'integrations',
                'label' => 'Integrations',
                'icon' => 'solar:plug-circle-linear',
                'resources' => [
                    ['value' => 'integrations', 'label' => 'Integrations hub'],
                ],
            ],
            [
                'key' => 'admissions',
                'label' => 'Admissions Setup',
                'icon' => 'solar:square-academic-cap-linear',
                'resources' => [
                    ['value' => 'nationality', 'label' => 'Nationalities'],
                    ['value' => 'admission_date', 'label' => 'Admission dates'],
                    ['value' => 'gender', 'label' => 'Genders'],
                    ['value' => 'relation_ship', 'label' => 'Relationships'],
                    ['value' => 'country', 'label' => 'Countries'],
                    ['value' => 'terms_condition', 'label' => 'Terms & conditions'],
                    ['value' => 'school', 'label' => 'Schools / campuses'],
                    ['value' => 'year', 'label' => 'Years'],
                    ['value' => 'language', 'label' => 'Languages'],
                    ['value' => 'subject', 'label' => 'Subjects'],
                    ['value' => 'package', 'label' => 'Packages'],
                    ['value' => 'course', 'label' => 'Courses'],
                    ['value' => 'admission_studetns', 'label' => 'Student submissions'],
                ],
            ],
            [
                'key' => 'forms',
                'label' => 'Forms & Applications',
                'icon' => 'solar:document-add-linear',
                'resources' => [
                    ['value' => 'staff_application_form', 'label' => 'Staff applications'],
                    ['value' => 'job', 'label' => 'Job applications'],
                    ['value' => 'apply', 'label' => 'Apply now'],
                    ['value' => 'enquire', 'label' => 'Enquiries'],
                    ['value' => 'referral', 'label' => 'Referrals'],
                    ['value' => 'subscribe', 'label' => 'Subscriptions'],
                ],
            ],
            [
                'key' => 'events',
                'label' => 'Events',
                'icon' => 'solar:calendar-linear',
                'resources' => [
                    ['value' => 'open_event', 'label' => 'Open events'],
                    ['value' => 'event_item', 'label' => 'Event items'],
                    ['value' => 'meet_speaker', 'label' => 'Speakers'],
                    ['value' => 'open_event_form', 'label' => 'Event submissions'],
                ],
            ],
            [
                'key' => 'website',
                'label' => 'Website & Settings',
                'icon' => 'solar:monitor-smartphone-linear',
                'resources' => [
                    ['value' => 'setting', 'label' => 'Website CMS / settings'],
                ],
            ],
            [
                'key' => 'custom',
                'label' => 'Custom area',
                'icon' => 'solar:magic-stick-3-linear',
                'resources' => [],
                'allow_custom' => true,
            ],
        ];
    }

    /** @return array<int, array{action: string, resource: string, label: string}> */
    public static function permissionBuilderPresets(): array
    {
        return [
            ['action' => 'view', 'resource' => 'leads', 'label' => 'View leads'],
            ['action' => 'export', 'resource' => 'leads', 'label' => 'Export leads'],
            ['action' => 'view', 'resource' => 'invoices', 'label' => 'View invoices'],
            ['action' => 'send', 'resource' => 'quotations', 'label' => 'Send quotations'],
            ['action' => 'view', 'resource' => 'form submissions', 'label' => 'View form submissions'],
            ['action' => 'manage', 'resource' => 'crm documents', 'label' => 'Manage CRM documents'],
        ];
    }

    public static function buildPermissionName(string $action, string $resource): string
    {
        $action = strtolower(trim($action));
        $resource = strtolower(trim(preg_replace('/\s+/', ' ', $resource) ?? ''));

        return trim("{$action} {$resource}");
    }

    public static function normalizePermissionName(string $name): string
    {
        $parsed = self::parsePermissionName($name);

        if ($parsed['action'] === '' || $parsed['resource'] === '') {
            return strtolower(trim($name));
        }

        return self::buildPermissionName($parsed['action'], $parsed['resource']);
    }

    /** @return array{action: string, resource: string} */
    public static function parsePermissionName(string $name): array
    {
        $parts = explode(' ', strtolower(trim($name)), 2);

        return [
            'action' => $parts[0] ?? '',
            'resource' => $parts[1] ?? '',
        ];
    }
}
