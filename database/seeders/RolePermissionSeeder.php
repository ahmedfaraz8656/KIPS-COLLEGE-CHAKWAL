<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage settings', 'manage users', 'manage backups', 'view audit trail',
            'manage students', 'view students', 'manage teachers', 'view teachers',
            'create exam', 'enter marks', 'view results', 'manage grading',
            'mark attendance', 'view attendance', 'manage holidays',
            'manage fees', 'view fees',
            'manage notices', 'manage calendar', 'manage timetable',
            'view own results', 'view own attendance', 'view own fees',
            'view child results', 'view child attendance', 'view child fees',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $roles = [
            'Managing Director' => $permissions, // full access
            'Principal' => array_diff($permissions, ['manage backups']),
            'Admin' => array_diff($permissions, ['manage backups', 'view audit trail']),
            'Exam Controller' => ['create exam', 'enter marks', 'view results', 'manage grading', 'view students'],
            'Teacher' => ['enter marks', 'view results', 'view students', 'view attendance'],
            'Class Incharge' => ['mark attendance', 'view attendance', 'view students', 'enter marks', 'view results'],
            'Student' => ['view own results', 'view own attendance', 'view own fees'],
            'Parent' => ['view child results', 'view child attendance', 'view child fees'],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePerms);
        }
    }
}
