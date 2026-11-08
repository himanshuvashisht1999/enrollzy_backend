<?php

use Database\Seeders\CareerRoadmapRestructureSeeder;
use Illuminate\Database\Migrations\Migration;

/** The Career Roadmap content pass (types, order, facts, links, content fixes, stream details, careers): see the seeder. */
return new class extends Migration
{
    public function up(): void
    {
        (new CareerRoadmapRestructureSeeder())->run();
    }

    public function down(): void
    {
        // content changes are not undone
    }
};
