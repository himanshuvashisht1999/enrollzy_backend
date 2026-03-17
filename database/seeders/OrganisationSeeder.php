<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orgs = [
            [
                'name' => 'Amity University',
                'mode' => 'Online / Distance',
                'approvals' => 'UGC, AICTE',
                'fees' => '₹1,50,000',
                'placement' => 'Yes',
                'rating' => 4.5
            ],
            [
                'name' => 'Manipal University',
                'mode' => 'Online',
                'approvals' => 'UGC',
                'fees' => '₹1,80,000',
                'placement' => 'Yes',
                'rating' => 4.6
            ],
            [
                'name' => 'LPU',
                'mode' => 'Online / Regular',
                'approvals' => 'UGC, NAAC A++',
                'fees' => '₹1,20,000',
                'placement' => 'Yes',
                'rating' => 4.4
            ],
            [
                'name' => 'Chandigarh University',
                'mode' => 'Online',
                'approvals' => 'UGC, NAAC A+',
                'fees' => '₹1,30,000',
                'placement' => 'Yes',
                'rating' => 4.3
            ],
            [
                'name' => 'Jain University',
                'mode' => 'Online',
                'approvals' => 'UGC',
                'fees' => '₹1,10,000',
                'placement' => 'Limited',
                'rating' => 4.2
            ],
        ];

        foreach ($orgs as $org) {
            Organisation::updateOrCreate(['name' => $org['name']], $org);
        }
    }
}
