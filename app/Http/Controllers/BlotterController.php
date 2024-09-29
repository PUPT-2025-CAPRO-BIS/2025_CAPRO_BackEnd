<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;


class BlotterController extends Controller
{
    public function fileBlotterReport(Request $request)
    {
        if(!!$request->complainee_name && !!$request->complainee_id)
        {
            return response()->json([
                'error_msg' => 'If resident, pass the id. If complainee is not a resident just pass a name',
                'error' => true,
                'success' => false
            ]);
        }
        $complainee_name = !$request->complainee_name || $request->complainee_name == '' ? null : $request->complainee_name;
        $complainee_id = !$request->complainee_id || $request->complainee_id == '' ? null : $request->complainee_id;
        $complainant_name = !$request->complainant_name || $request->complainant_name == '' ? null : $request->complainant_name;
        $complainant_id = !$request->complainant_id || $request->complainant_id == '' ? null : $request->complainant_id;
        $admin_id = session("UserId");
        $complaint_remarks = $request->complaint_remarks;
        //$complaint_file = $request->base64_file;
        //Statuses = 0 Ongoing
        //Statuses = 1 Settled
        //Statuses = 2 Unresolved
        //Statuses = 3 Dismissed
        $current_date = date('Y-m-d H:i:s');
        if(is_null($request->status_resolved))
        {
            $status_resolved = 0;
        }
        else
        {
            $status_resolved = $request->status_resolved;
        }

        DB::table('blotter_reports')
            ->insert([
                'complainee_name' => $complainee_name,
                'complainant_name' => $complainant_name,
                'complainant_id' => $complainant_id,
                'admin_id' => $admin_id,
                'complainee_id' => $complainee_id,
                'complaint_remarks' => $complaint_remarks,
                //'complaint_file' => $complaint_file,
                'created_at' => $current_date,
                'updated_at' => $current_date,
                'status_resolved' => $status_resolved,
                'officer_on_duty' => $request->officer_on_duty
            ]);
        return response()->json([
            'msg' => 'A blotter report has been filed',
            'success' => true
        ]);
    }
    public function editBlotterReport(Request $request)
    {
        if(!!$request->complainee_name && !!$request->complainee_id)
        {
            return response()->json([
                'error_msg' => 'If resident, pass the id. If complainee is not a resident just pass a name',
                'error' => true,
                'success' => false
            ]);
        }
        $blotter_id = $request->id;
        $complainee_name = !$request->complainee_name || $request->complainee_name == '' ? null : $request->complainee_name;
        $complainee_id = !$request->complainee_id || $request->complainee_id == '' ? null : $request->complainee_id;
        $complainant_name = !$request->complainant_name || $request->complainant_name == '' ? null : $request->complainant_name;
        $complainant_id = !$request->complainant_id || $request->complainant_id == '' ? null : $request->complainant_id;
        $admin_id = session("UserId");
        
        $complaint_remarks = $request->complaint_remarks;
        $current_date = date('Y-m-d H:i:s');
        $status_resolved = $request->status_resolved;

        DB::table('blotter_reports')
            ->where('id','=',$blotter_id)
            ->update([
                'complainee_name' => $complainee_name,
                'complainant_name' => $complainant_name,
                'complainant_id' => $complainant_id,
                'complainee_id' => $complainee_id,
                'admin_id' => $admin_id,
                'complaint_remarks' => $complaint_remarks,
                'updated_at' => $current_date,
                'status_resolved' => $status_resolved,
                'officer_on_duty' => $request->officer_on_duty
            ]);
            /*
        if($request->base64_file)
        {
            DB::table('blotter_reports')
            ->where('id','=',$blotter_id)
            ->update([
                'complaint_file' => $request->base64_file
            ]);
        }
        */
        return response()->json([
            'msg' => 'Blotter has been updated',
            'success' => true
        ]);
    }
    public function viewAllBlotters(Request $request)
    {
        
        $user_id = session("UserId");
        /*
        $item_per_page = $request->item_per_page;
        $page_number = $request->page_number;

        $offset = $item_per_page * ($page_number - 1);
        $offset_value = '';
        if($offset != 0)
        {
            $offset_value = 'OFFSET ' . ($item_per_page * ($page_number - 1));
        }
        $search_value = '';
        if($request->search_value)
        {
            $search_value = "WHERE first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%'";
        }
            */

        $item_per_page_limit ="";
        $item_per_page = "";
        $offset = 0;
        $page_number = $request->page_number;
        if($request->item_per_page)
        {
            $item_per_page = $request->item_per_page;
            $offset = $item_per_page * ($page_number - 1);
            $item_per_page_limit = "LIMIT $request->item_per_page";
        }
        $offset_value = '';
        if($offset != 0)
        {
            $offset_value = 'OFFSET ' . ($item_per_page * ($page_number - 1));
        }
        $search_value = '';
        if($request->search_value)
        {
            $search_value = 
            "WHERE
            complainee_name like '%$request->search_value%' OR ".

            "complainant_name like '%$request->search_value%' OR ".

            "ceu.first_name like '%$request->search_value%' OR ".
            "ceu.middle_name like '%$request->search_value%' OR " .
            "ceu.last_name like '%$request->search_value%' OR " .
            
            "cau.first_name like '%$request->search_value%' OR ".
            "cau.middle_name like '%$request->search_value%' OR " .
            "cau.last_name like '%$request->search_value%' OR " .

            "br.complainee_name like '%$request->search_value%' OR ".
            "br.complainant_name like '%$request->search_value%'";
        }



        $blotters = DB::select("SELECT
        br.id,
        CASE WHEN br.complainee_name IS NULL THEN CONCAT(ceu.first_name, (CASE WHEN ceu.middle_name = '' THEN '' ELSE ' ' END),ceu.middle_name,' ',ceu.last_name) ELSE br.complainee_name END as complainee_name,
        CASE WHEN br.complainant_name IS NULL THEN CONCAT(cau.first_name, (CASE WHEN cau.middle_name = '' THEN '' ELSE ' ' END),cau.middle_name,' ',cau.last_name) ELSE br.complainant_name END as complainant_name,
        br.complainee_id,
        br.complainant_id,
        br.admin_id,
        br.complaint_remarks,
        br.status_resolved,
        br.created_at,
        CONCAT(au.first_name, (CASE WHEN au.middle_name = '' THEN '' ELSE ' ' END),au.middle_name,' ',au.last_name) as admin_name,
        br.officer_on_duty,
        CASE WHEN br.complainee_id IS NULL THEN 0 ELSE 1 END as is_complainee_resident,
        CASE WHEN br.complainant_id IS NULL THEN 0 ELSE 1 END as is_complainant_resident

        FROM(
        SELECT *
        FROM blotter_reports
        ) as br
        LEFT JOIN users as au on au.id = br.admin_id
        LEFT JOIN users as ceu on ceu.id = br.complainee_id
        LEFT JOIN users as cau on cau.id = br.complainant_id
        $search_value
        ORDER BY br.id DESC
        $item_per_page_limit
        $offset_value
        ");
        $blotters = array_map(function($obj) {
            unset($obj->cu_id);
            return $obj;
        },$blotters);

        $total_pages = DB::select("SELECT
        count(br.id) as page_count
        FROM(
        SELECT *
        FROM blotter_reports
        ) as br
        LEFT JOIN users as au on au.id = br.admin_id
        LEFT JOIN users as ceu on ceu.id = br.complainee_id
        LEFT JOIN users as cau on cau.id = br.complainant_id
        $search_value
        ORDER BY br.id DESC
        ")[0]->page_count;
        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$blotters,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
        //return response()->json($users,200);
    }

    public function downloadBlotterPDF(Request $request)
    {
        $blotter_details = DB::table('blotter_reports')
            ->where('id', $request->blotter_id)
            ->first();

        if (!$blotter_details) {
            return response()->json(['error' => 'Blotter not found'], 404);
        }

        $complainant_name = $blotter_details->complainant_name ?? 'N/A';
        $complainant_address = 'N/A';
        $complainant_phone = $blotter_details->complainant_phone_number ?? 'N/A'; 
        $complainee_name = $blotter_details->complainee_name ?? 'N/A';
        $complainee_address = 'N/A';
        $complainee_phone = $blotter_details->complainee_phone_number ?? 'N/A'; 

        if ($blotter_details->complainant_id) {
            $complainant = DB::table('users')
                ->where('id', $blotter_details->complainant_id)
                ->select('current_address')
                ->first();

            if ($complainant) {
                $complainant_address = $complainant->current_address ?? 'N/A';
            }
        }

        if ($blotter_details->complainee_id) {
            $complainee = DB::table('users')
                ->where('id', $blotter_details->complainee_id)
                ->select('current_address')
                ->first();

            if ($complainee) {
                $complainee_address = $complainee->current_address ?? 'N/A';
            }
        }

        if (!$blotter_details->complainant_id) {
            $complainant_address = $blotter_details->non_resident_address ?? 'N/A';
        }

        if (!$blotter_details->complainee_id) {
            $complainee_address = $blotter_details->non_resident_address ?? 'N/A';
        }

        $data = [
            'title' => 'Blotter Report',
            'blotter_details' => $blotter_details,
            'complainant_name' => $complainant_name,
            'complainant_address' => $complainant_address,
            'complainant_phone' => $complainant_phone, 
            'complainee_name' => $complainee_name,
            'complainee_address' => $complainee_address,
            'complainee_phone' => $complainee_phone  
        ];

        $pdf = PDF::loadView('document.blotter', $data);
        $pdf->setPaper('A4', 'portrait');

        return $request->download == 1 ? $pdf->download('blotter_report.pdf') : $pdf->stream('blotter_report.pdf');
    }
}
