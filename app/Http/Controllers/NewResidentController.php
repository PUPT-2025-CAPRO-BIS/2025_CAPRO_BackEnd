<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class NewResidentController extends Controller
{
    public function viewNewResidentRequests(Request $request)
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
            $search_value = "AND (first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%')";
        }



        $users = DB::select("SELECT
        u.id,
        u.Email,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.civil_status_id,
        ct.civil_status_type,
        u.male_female,
        u.birthday,
        u.cell_number,
        DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS age,
        CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
        CASE WHEN bo.id IS NOT NULL THEN 0 ELSE 1 END as assignable_brgy_official,
        CASE WHEN ur.role_id IN ('2','3') THEN 0 ELSE 1 END as assignable_admin,
        (
            SELECT
            GROUP_CONCAT(id)
            FROM supporting_files
            WHERE user_id = u.id
        ) as supporting_file_ids
        FROM(
        SELECT *
        FROM users
        WHERE id != '$user_id'
        AND isPendingResident = '1'
        $search_value
        ORDER BY id
        $item_per_page_limit
        $offset_value
        ) as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id       
        ");
        foreach($users as $user)
        {
            $user->supporting_file_ids = explode(',', $user->supporting_file_ids);
        }
        $total_pages = DB::select("SELECT
        count(id) as page_count
        FROM users
        WHERE id != '$user_id'
        $search_value
        AND isPendingResident = '1'
        ORDER BY id
        ")[0]->page_count;

        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$users,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
    }
    public function approveNewResident(Request $request)
    {
        DB::table('users')
            ->where('id','=',$request->user_id)
            ->update([
                'isPendingResident' => '0'
            ]);
        return response()->json([
            'msg' => 'New resident has been approved',
            'success' => true
        ]);
    }
}