<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // 1. Roles Table
        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey) {
            if (!Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after('id');
                $table->index($teamForeignKey, 'roles_team_foreign_key_index');
                
                // Drop existing unique constraint and add new one including team_id
                $table->dropUnique('roles_name_guard_name_unique');
                $table->unique([$teamForeignKey, 'name', 'guard_name']);
            }
        });

        // 2. Model Has Roles Table
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teamForeignKey) {
            if (!Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->default(0); // Default 0 for existing records
                $table->index($teamForeignKey, 'model_has_roles_team_foreign_key_index');

                // Drop Foreign Key first to allow dropping index
                $table->dropForeign('model_has_roles_role_id_foreign');
                
                // Drop Primary Key
                $table->dropPrimary();
                
                // Add index for role_id to support foreign key (since it's no longer first in PK)
                $table->index($columnNames['role_pivot_key'] ?? 'role_id', 'model_has_roles_role_id_index');

                // Update primary key to include team_id
                $table->primary(
                    [$teamForeignKey, $columnNames['role_pivot_key'] ?? 'role_id', $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_roles_primary'
                );

                // Restore Foreign Key
                $table->foreign($columnNames['role_pivot_key'] ?? 'role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();
            }
        });

        // 3. Model Has Permissions Table
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teamForeignKey) {
            if (!Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->unsignedBigInteger($teamForeignKey)->default(0);
                $table->index($teamForeignKey, 'model_has_permissions_team_foreign_key_index');

                // Drop Foreign Key first
                $table->dropForeign('model_has_permissions_permission_id_foreign');

                // Drop Primary Key
                $table->dropPrimary();
                
                // Add index for permission_id
                $table->index($columnNames['permission_pivot_key'] ?? 'permission_id', 'model_has_permissions_permission_id_index');

                // Update Primary Key
                $table->primary(
                    [$teamForeignKey, $columnNames['permission_pivot_key'] ?? 'permission_id', $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_permissions_primary'
                );

                // Restore Foreign Key
                $table->foreign($columnNames['permission_pivot_key'] ?? 'permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

        if (empty($tableNames)) {
            return;
        }

        // Revert Model Has Permissions
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teamForeignKey) {
             if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                // Drop Foreign Key
                $table->dropForeign(['permission_id']); // Array syntax drops based on column name convention

                $table->dropPrimary('model_has_permissions_primary');
                $table->dropIndex('model_has_permissions_permission_id_index');

                $table->primary(
                    [$columnNames['permission_pivot_key'] ?? 'permission_id', $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
                
                $table->dropIndex('model_has_permissions_team_foreign_key_index');
                $table->dropColumn($teamForeignKey);

                // Restore Foreign Key
                $table->foreign($columnNames['permission_pivot_key'] ?? 'permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();
             }
        });

        // Revert Model Has Roles
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teamForeignKey) {
             if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                // Drop Foreign Key
                $table->dropForeign(['role_id']);

                $table->dropPrimary('model_has_roles_primary');
                $table->dropIndex('model_has_roles_role_id_index');

                $table->primary(
                    [$columnNames['role_pivot_key'] ?? 'role_id', $columnNames['model_morph_key'] ?? 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
                
                $table->dropIndex('model_has_roles_team_foreign_key_index');
                $table->dropColumn($teamForeignKey);

                // Restore Foreign Key
                $table->foreign($columnNames['role_pivot_key'] ?? 'role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();
             }
        });

        // Revert Roles
        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey) {
             if (Schema::hasColumn($table->getTable(), $teamForeignKey)) {
                $table->dropUnique([$teamForeignKey, 'name', 'guard_name']);
                $table->unique(['name', 'guard_name']);
                
                $table->dropIndex('roles_team_foreign_key_index');
                $table->dropColumn($teamForeignKey);
             }
        });
    }
};
