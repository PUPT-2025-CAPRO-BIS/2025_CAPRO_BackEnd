<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class BarangayOfficialController extends Controller
{
    public function assignBarangayOfficial(Request $request)
    {
        $user_id = $request->user_id;
        $user_details = DB::select("SELECT
        u.id,
        u.first_name,
        u.middle_name,
        u.last_name,
        bo.id as barangay_official_id
        FROM users as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        where u.id = '$user_id'
         ");
        if(count($user_details) < 1)
        {
            return response()->json([
                'error_msg' => 'User with specified id does not exist',
                'success' => false
            ],401);
        }
        if(!is_null($user_details[0]->barangay_official_id))
        {
            return response()->json([
                'error_msg' => 'User already has a barangay official entry',
                'success' => false
            ],401);
        }
        $chairmanship = $request->chairmanship;
        $position = $request->position;

        DB::statement("INSERT INTO
        barangay_officials
        (user_id,chairmanship,position,status)
        VALUES ('$user_id','$chairmanship','$position','1')
        ");

        return response()->json([
            'msg' => 'User has been assigned as a barangay official',
            'success' => true
        ],200);
    }
    /*public function viewAssignableToBarangayOfficial()
    {

        return DB::select("SELECT
            u.id,
            CONCAT(u.first_name,' ',u.middle_name,'',u.last_name) as full_name
            FROM users as u
            LEFT JOIN barangay_officials as bo on bo.user_id = u.id
            where bo.id IS NULL
        ");
    }*/
    public function viewBarangayOfficials(Request $request)
    {
        $item_per_page = "";
        $item_per_page_limit ="";
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
            $search_value = "WHERE first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%'";
        }
        $barangay_officials = DB::select("SELECT
        u.id as user_id,
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
        bo.chairmanship,
        bo.position,
        bo.status
        FROM barangay_officials as bo
        LEFT JOIN users as u on bo.user_id = u.id
        $search_value
        ORDER BY bo.id
        $item_per_page_limit
        $offset_value
        ");
        $total_pages = DB::select("SELECT
        count(u.id) as page_count
        FROM barangay_officials as bo
        LEFT JOIN users as u on bo.user_id = u.id
        $search_value
        ORDER BY bo.id
        ")[0]->page_count;
        $total_pages = ceil($total_pages/$item_per_page );
        return response()->json(['data'=>$barangay_officials,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
    }
    public function changeBarangayOfficialDetails(Request $request)
    {
        $new_status = $request->new_status;
        $user_id = $request->user_id;
        $update_string = 'SET ';
        $update_string .= !is_null($request->chairmanship) ? "chairmanship = '$request->chairmanship'," : '';
        $update_string .= !is_null($request->position) ? "position = '$request->position'," : '';
        $update_string .= !is_null($request->status) ? "status = '$request->status'," : '';
        $update_string = rtrim($update_string, ',');
        $bo_details = DB::SELECT("SELECT
        user_id,
        status
        FROM
        barangay_officials
        where user_id = '$user_id'
        ");
        if(count($bo_details) < 1)
        {
            return response()->json([
                'error_msg' => 'Barangay official record does not exist',
                'success' => false
            ],401);
        }
        DB::statement("UPDATE
        barangay_officials
        $update_string
        WHERE user_id = '$user_id'
        ");
        return response()->json([
            'msg' => 'Barangay official record status has been changed',
            'success' => true
        ],200);
    }
    public function deleteBarangayOfficial(Request $request)
    {
        $user_id = $request->user_id;
        $bo_details = DB::select("SELECT
        *
        FROM barangay_officials
        WHERE user_id = '$user_id'
        ");
        if(count($bo_details) < 1)
        {
            return response()->json([
                'error_msg' => 'Barangay official record does not exist',
                'success' => false
            ],401);
        }
        DB::statement("DELETE
        FROM barangay_officials
        WHERE user_id = '$user_id'
        ");
        return response([
            'msg' => 'Barangay official record has been deleted',
            'success' => true
        ],200);
    }
}
