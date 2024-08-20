<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;


class BlotterController extends Controller
{
    public function fileBlotterReport(Request $request)
    {
        $complainee_name = $request->complainee_name;
        $complainant_id = $request->complainant_user_id;
        $admin_id = session("UserId");
        
        $complaint_remarks = $request->complaint_remarks;
        $complaint_file = $request->base64_file;
        $current_date = date('Y-m-d H:i:s');
        $status_resolved = false;

        DB::table('blotter_reports')
            ->insert([
                'complainee_name' => $complainee_name,
                'complainant_id' => $complainant_id,
                'admin_id' => $admin_id,
                'complaint_remarks' => $complaint_remarks,
                'complaint_file' => $complaint_file,
                'created_at' => $current_date,
                'updated_at' => $current_date,
                'status_resolved' => 0
            ]);
        return response()->json([
            'msg' => 'A blotter report has been filed',
            'success' => true
        ]);
    }
    public function editBlotterReport(Request $request)
    {
        $complainee_id = $request->$complainee_name;
        $complainant_id = $request->$complainant_user_id;
        $admin_id = session("UserId");
        
        $complaint_remarks = $request->complaint_remarks;
        $complaint_file = $request->base64_file;
        $current_date = date('Y-m-d H:i:s');
        $status_resolved = false;

        DB::table('blotter_reports')
            ->where()
            ->update([
                'complainee_name' => $complainee_name,
                'complainant_user_id' => $complainant_user_id,
                'admin_id' => $admin_id,
                'complaint_remarks' => $complaint_remarks,
                'complaint_file' => $complaint_file,
                'updated_at' => $current_date,
                'status_resolved' => 0
            ]);
    }
}