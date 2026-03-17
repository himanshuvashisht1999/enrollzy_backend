<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Expert;
use App\Models\CommissionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Expert Categories Table
        Schema::create('expert_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add Foreign Keys (Nullable first for migration)
        Schema::table('experts', function (Blueprint $table) {
            $table->foreignId('expert_category_id')->nullable()->after('role')->constrained('expert_categories')->nullOnDelete();
        });

        Schema::table('commission_policies', function (Blueprint $table) {
            $table->foreignId('expert_category_id')->nullable()->after('expert_category')->constrained('expert_categories')->nullOnDelete();
        });

        // 3. Migrate Data
        // Fetch all unique roles from Experts
        $roles = Expert::distinct()->whereNotNull('role')->pluck('role');
        
        // Also fetch any categories defined in policies that might not have experts yet (unlikely but safe)
        $policyCategories = CommissionPolicy::where('policy_type', 'category')->distinct()->pluck('expert_category');
        
        $allCategories = $roles->merge($policyCategories)->unique()->filter();

        foreach ($allCategories as $categoryName) {
            $slug = Str::slug($categoryName);
            // Check if exists (unlikely given unique logic but good practice)
            $catId = DB::table('expert_categories')->insertGetId([
                'name' => $categoryName,
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Link Experts
            Expert::where('role', $categoryName)->update(['expert_category_id' => $catId]);

            // Link Commission Policies
            CommissionPolicy::where('policy_type', 'category')
                ->where('expert_category', $categoryName)
                ->update(['expert_category_id' => $catId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropForeign(['expert_category_id']);
            $table->dropColumn('expert_category_id');
        });

        Schema::table('commission_policies', function (Blueprint $table) {
            $table->dropForeign(['expert_category_id']);
            $table->dropColumn('expert_category_id');
        });

        Schema::dropIfExists('expert_categories');
    }
};
