<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_types')) {
            Schema::create('project_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('color', 50)->default('primary');
                $table->string('status', 50)->default('active');
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            // Seed initial project types
            $defaultTypes = [
                ['name' => 'Internal', 'code' => 'INT', 'color' => 'primary', 'description' => 'Internal organizational projects'],
                ['name' => 'Client', 'code' => 'CLI', 'color' => 'success', 'description' => 'Direct client deliverables and projects'],
                ['name' => 'Marketing Campaign', 'code' => 'MKT', 'color' => 'info', 'description' => 'Marketing, branding, and student campaigns'],
                ['name' => 'Technology / Dev', 'code' => 'DEV', 'color' => 'dark', 'description' => 'Software development and IT infrastructure'],
                ['name' => 'Operations', 'code' => 'OPS', 'color' => 'warning', 'description' => 'Company operations and administrative processes'],
                ['name' => 'Partner Collaborative', 'code' => 'PTR', 'color' => 'secondary', 'description' => 'Joint initiatives with external agencies and partners'],
            ];

            foreach ($defaultTypes as $type) {
                DB::table('project_types')->insert([
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'color' => $type['color'],
                    'description' => $type['description'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Add columns to projects table
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'project_type_id')) {
                $table->unsignedBigInteger('project_type_id')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('projects', 'project_type')) {
                $table->string('project_type', 100)->nullable()->after('project_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'project_type_id')) {
                $table->dropColumn('project_type_id');
            }
            if (Schema::hasColumn('projects', 'project_type')) {
                $table->dropColumn('project_type');
            }
        });

        Schema::dropIfExists('project_types');
    }
};
