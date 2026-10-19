<?php

namespace Database\Seeders;

use App\Models\ExamModule\City;
use Illuminate\Database\Seeder;

/**
 * Starter list for the Exam Module City master (exam centres): state capitals plus the cities
 * where national exams commonly hold centres. Safe to re-run: existing cities are left untouched.
 */
class ExamCitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Kurnool', 'Nellore', 'Kakinada', 'Rajahmundry', 'Anantapur', 'Amaravati'],
            'Arunachal Pradesh' => ['Itanagar'],
            'Assam' => ['Guwahati', 'Dibrugarh', 'Silchar', 'Jorhat', 'Tezpur'],
            'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Darbhanga', 'Purnia'],
            'Chhattisgarh' => ['Raipur', 'Bhilai', 'Bilaspur', 'Korba'],
            'Goa' => ['Panaji', 'Margao'],
            'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Gandhinagar', 'Bhavnagar', 'Jamnagar'],
            'Haryana' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala', 'Hisar', 'Rohtak', 'Karnal'],
            'Himachal Pradesh' => ['Shimla', 'Dharamshala', 'Solan', 'Mandi'],
            'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro Steel City'],
            'Karnataka' => ['Bengaluru', 'Mysuru', 'Mangaluru', 'Hubballi', 'Belagavi', 'Kalaburagi', 'Davanagere'],
            'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Kannur', 'Kottayam', 'Palakkad'],
            'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur', 'Ujjain', 'Sagar', 'Rewa'],
            'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad', 'Thane', 'Navi Mumbai', 'Kolhapur', 'Solapur', 'Amravati'],
            'Manipur' => ['Imphal'],
            'Meghalaya' => ['Shillong'],
            'Mizoram' => ['Aizawl'],
            'Nagaland' => ['Kohima', 'Dimapur'],
            'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Berhampur', 'Sambalpur'],
            'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali'],
            'Rajasthan' => ['Jaipur', 'Jodhpur', 'Kota', 'Udaipur', 'Ajmer', 'Bikaner', 'Alwar', 'Sikar'],
            'Sikkim' => ['Gangtok'],
            'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Vellore', 'Erode'],
            'Telangana' => ['Hyderabad', 'Warangal', 'Karimnagar', 'Nizamabad', 'Khammam'],
            'Tripura' => ['Agartala'],
            'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Varanasi', 'Prayagraj', 'Agra', 'Meerut', 'Ghaziabad', 'Noida', 'Gorakhpur', 'Bareilly', 'Aligarh', 'Moradabad', 'Jhansi'],
            'Uttarakhand' => ['Dehradun', 'Haridwar', 'Haldwani', 'Roorkee'],
            'West Bengal' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Kharagpur'],
            'Andaman and Nicobar Islands' => ['Port Blair'],
            'Chandigarh' => ['Chandigarh'],
            'Dadra and Nagar Haveli and Daman and Diu' => ['Daman', 'Silvassa'],
            'Delhi' => ['New Delhi', 'Delhi'],
            'Jammu and Kashmir' => ['Srinagar', 'Jammu'],
            'Ladakh' => ['Leh'],
            'Lakshadweep' => ['Kavaratti'],
            'Puducherry' => ['Puducherry'],
        ];

        $added = 0;
        foreach ($cities as $state => $names) {
            foreach ($names as $name) {
                $city = City::firstOrCreate(['name' => $name, 'state' => $state], ['status' => true]);
                $added += $city->wasRecentlyCreated ? 1 : 0;
            }
        }
        $this->command?->info("Exam cities: $added added.");
    }
}
