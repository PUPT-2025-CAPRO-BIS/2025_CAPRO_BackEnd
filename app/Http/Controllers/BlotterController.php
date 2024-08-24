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
        
        $blotter_id = $request->id;
        $complainee_name = $request->complainee_name;
        $complainant_id = $request->complainant_id;
        $admin_id = session("UserId");
        
        $complaint_remarks = $request->complaint_remarks;
        $current_date = date('Y-m-d H:i:s');
        $status_resolved = $request->status_resolved;

        DB::table('blotter_reports')
            ->where('id','=',$blotter_id)
            ->update([
                'complainee_name' => $complainee_name,
                'complainant_id' => $complainant_id,
                'admin_id' => $admin_id,
                'complaint_remarks' => $complaint_remarks,
                'updated_at' => $current_date,
                'status_resolved' => $status_resolved
            ]);
        if($request->base64_file)
        {
            DB::table('blotter_reports')
            ->where('id','=',$blotter_id)
            ->update([
                'complaint_file' => $request->base64_file
            ]);
        }
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
            "cu.first_name like '%$request->search_value%' OR ".
            "cu.middle_name like '%$request->search_value%' OR " .
            "cu.last_name like '%$request->search_value%' OR" .

            "au.first_name like '%$request->search_value%' OR ".
            "au.middle_name like '%$request->search_value%' OR " .
            "au.last_name like '%$request->search_value%'" .
            ")";
        }



        $blotters = DB::select("SELECT
        *
        FROM(
        SELECT *
        FROM blotter_reports
        ) as br
        LEFT JOIN
        ( SELECT
            CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as complainant_name,
            u.id as cu_id
            FROM users as u
            LEFT JOIN blotter_reports as br on br.complainant_id = u.id
            WHERE u.id = br.complainant_id
        ) as cu on cu.cu_id = br.complainant_id
        LEFT JOIN
        ( SELECT
            CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as admin_name,
            u.id as cu_id
            FROM users as u
            LEFT JOIN blotter_reports as br on br.admin_id = u.id
            WHERE u.id = br.admin_id
        ) as au on au.cu_id = br.admin_id
        $search_value
        ORDER BY id
        $item_per_page_limit
        $offset_value
        ");
        $blotters = array_map(function($obj) {
            unset($obj->cu_id);
            return $obj;
        },$blotters);

        $total_pages = DB::select("SELECT
        count(id) as page_count
        FROM blotter_reports
        $search_value
        ORDER BY id
        ")[0]->page_count;
        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$blotters,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
        //return response()->json($users,200);
    }
}