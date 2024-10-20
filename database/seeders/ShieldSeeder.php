<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
  public function run(): void
  {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_district","view_any_district","create_district","update_district","delete_district","delete_any_district","view_province","view_any_province","create_province","update_province","delete_province","delete_any_province","view_regency","view_any_regency","create_regency","update_regency","delete_regency","delete_any_regency","view_shield::role","view_any_shield::role","create_shield::role","update_shield::role","delete_shield::role","delete_any_shield::role","view_user","view_any_user","create_user","update_user","delete_user","delete_any_user","view_village","view_any_village","create_village","update_village","delete_village","delete_any_village"]}]';
    $directPermissions = '[]';

    static::makeRolesWithPermissions($rolesWithPermissions);
    static::makeDirectPermissions($directPermissions);

    $this->command->info('Shield Seeding Completed.');
  }

  protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
  {
    if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
      $roleModel = Utils::getRoleModel();
      $permissionModel = Utils::getPermissionModel();

      foreach ($rolePlusPermissions as $rolePlusPermission) {
        $role = $roleModel::firstOrCreate([
          'name' => $rolePlusPermission['name'],
          'guard_name' => $rolePlusPermission['guard_name'],
        ]);

        if (! blank($rolePlusPermission['permissions'])) {
          $permissionModels = collect($rolePlusPermission['permissions'])
            ->map(fn($permission) => $permissionModel::firstOrCreate([
              'name' => $permission,
              'guard_name' => $rolePlusPermission['guard_name'],
            ]))
            ->all();

          $role->syncPermissions($permissionModels);
        }
      }
    }
  }

  public static function makeDirectPermissions(string $directPermissions): void
  {
    if (! blank($permissions = json_decode($directPermissions, true))) {
      $permissionModel = Utils::getPermissionModel();

      foreach ($permissions as $permission) {
        if ($permissionModel::whereName($permission)->doesntExist()) {
          $permissionModel::create([
            'name' => $permission['name'],
            'guard_name' => $permission['guard_name'],
          ]);
        }
      }
    }
  }
}
