<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;


class BlotterController extends Controller
{
    public function fileBlotterReport(Request $request)
    {
        $complainee_id = $request->$complainee_user_id;
        $complainant_id = $request->$complainant_user_id;
        $admin_id = session("UserId");
        
        $complaint_remarks = $request->complaint_remarks;
        $complaint_file = $request->base64_file;
        $current_date = date('Y-m-d H:i:s');
        $status_resolved = false;

        //DB::table('blotter_reports');



    }
}
