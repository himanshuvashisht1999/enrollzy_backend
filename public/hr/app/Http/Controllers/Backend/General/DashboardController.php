<?php

namespace App\Http\Controllers\Backend\General;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Users;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Spatie\Analytics\Period;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Spatie\Analytics\Facades\Analytics;
use DB;
use App\Models\AdminImage;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $countStaff = Admin::where('role', 'staff')->count();
        $count = array(
            'countStaff' => $countStaff,
        );
        // --------- Frontend Health--------------------------------------
        // $analyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(7));
        // $mostVisitedPages = Analytics::fetchMostVisitedPages(Period::days(7));
        // $topBrowsers = Analytics::fetchTopBrowsers(Period::days(7));
        // $userSessions = Analytics::performQuery(Period::days(7), 'ga:sessions', [
        //     'metrics' => 'ga:sessions'
        // ]);
        // $bounceRate = Analytics::performQuery(Period::days(7), 'ga:bounceRate', [
        //     'metrics' => 'ga:bounceRate'
        // ]);
        // --------- Frontend Health--------------------------------------
        date_default_timezone_set('Asia/Kolkata');
        // Get the current hour in 24-hour format
        $hour = date('H');
        // Determine the greeting based on the hour
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour >= 12 && $hour < 17) {
            $greeting = 'Good Afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            $greeting = 'Good Evening';
        } else {
            $greeting = 'Good Night';
        }
        $authName = Auth::guard('admin')->user()->name;
        // total nicesms
        $smsres= $this->executeSMS('https://api.nicesms.in/pushapi/getUserCredit?username=amitb&password=India786?');
        if($smsres != null){
            $smsarr = json_decode($smsres, true);
            $niceSms = $smsarr;
            // $smsarr['userCredit'];
        }else{
            $niceSms = "No Data";
        }
        // total fastsms
        $smsresfast= $this->executeFastSMS('https://www.fast2sms.com/dev/bulkV2?username=amitb&password=India786?');
        if($smsresfast != null){
            $smsresfast = json_decode($smsresfast, true);
            $fastSms = $smsresfast;
            // $smsarr['userCredit'];
        }else{
            $fastSms = "No Data";
        }
        // return $fastSms;
        $sucongratulation = 'Welcome! ' . $authName . ', ' . $greeting;
        Session::flash('sucongratulation', $sucongratulation);

        $attendance = Attendance::where('date', date('Y-m-d'))->whereNull('check_out')
            ->where('staff_id', Auth::guard('admin')->id())->first();


            $authuser = Auth::user()->id;
            $arFormData = DB::table('tasks')->where('assigned_to', $authuser)->get();
    
            $todayDate = Carbon::today(); 
    
            // Iterate over the records
            foreach ($arFormData as $task) {
                if ($task->id_recursive_task === 'yes' && !Carbon::parse($task->updated_at)->isToday() && $task->id_recursive_task === 'Daily') {
                    DB::table('tasks')
                        ->where('id', $task->id)  // Make sure to update the specific task
                        ->update(['status' => 'pending']);
                }
                if ($task->id_recursive_task === 'yes' && !Carbon::parse($task->updated_at)->isToday() && $task->id_recursive_task === 'weekly') {
                    
                    if (Carbon::today()->isSaturday()) {
                        // Update the task's status to 'pending'
                        DB::table('tasks')
                            ->where('id', $task->id)  // Make sure to update the specific task
                            ->update(['status' => 'pending']);
                    }
                }
            }

        $user = Auth::user();
        $images = AdminImage::where('admin_id', $user->id)->latest('created_at')->get();
        $referenceImages = $images->map(function($img) {
            return [
                'id' => $img->id,
                'url' => asset('assets/user_attendance/' . $img->image)
            ];
        })->values()->all();



        return view('dashboard', compact('count', 'attendance','niceSms','referenceImages'));
    }
    public function fetchPinCode(Request $request)
    {
        $ApiResponse = Http::get('https://api.postalpincode.in/pincode/' . $request->term);
        $response = json_decode($ApiResponse, true);
        if ($response[0]['Status'] == 'Success') {
            return $response[0]['PostOffice'];
        } else {
            return array(
                [
                    'Name' => 'Nothing Found, Please Try Again',
                    'State' => '',
                    'Pincode' => '',
                ],
            );
        }
    }
    public function getProductQuantityLog()
    {
        $logs = [];
        $file = storage_path('logs/product_stock.log');
        if (file_exists($file)) {
            $contents = file_get_contents($file);
            $lines = explode("\n", $contents);
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $log = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // अगर message को एक बार और JSON डिकोड करना है
                        if (isset($log['message']) && is_string($log['message'])) {
                            $log['message'] = json_decode($log['message'], true);
                        }
                        $logs[] = $log;
                    }
                }
            }
        }
        echo '<pre>';
        print_r($logs);
        die;
    }

    public function clearCacheAdmin()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('optimize:clear');
            sendNotify(null, 'cache_clear', 'info', 'Cache cleared Successfully', 'high', 'admin', null);
            // function name ('userId = null for all users ', 'for', 'type', 'message', 'priority', 'visible for', 'target id');
            return redirect()->back()->with('success', 'Cache cleared successfully!');
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Cache clear error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to clear cache. Please try again : ' . $e->getMessage());
        }
    }

    function executeSMS($url)
	{
		$ch=curl_init();
		//curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, '0');
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, '0');
		$result = curl_exec($ch);

        $error_msg = curl_error($ch);
        $info = curl_getinfo($ch);

        //print_r($info);
        //echo "<br/><br/>";
        //print_r($info);

        curl_close($ch);
            //echo $output;
            //return $output;
        return $result;
    }

    function executeFastSMS($url)
	{
		$ch=curl_init();
		//curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, '0');
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, '0');
		$result = curl_exec($ch);

        $error_msg = curl_error($ch);
        $info = curl_getinfo($ch);

        //print_r($info);
        //echo "<br/><br/>";
        //print_r($info);

        curl_close($ch);
            //echo $output;
            //return $output;
        return $result;
    }

    
}
