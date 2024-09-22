<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicMail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DateTime;
use DateTimeZone;
require_once app_path('Helpers/helpers.php');
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
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
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
    public function editNewResidentStatus(Request $request)
    {
        $approve_reject = $request->approve_reject;
        if($approve_reject == 0)
        {
            $user_details = DB::table('users')
            ->where('id','=',$request->user_id)
            ->get();
            if(count($user_details)<1)
            {
                return response()->json([
                    'error_msg' => 'New resident request does not exist',
                    'error' => true
                ]);
            }
            $first_name = $user_details[0]->first_name;
            $subject  = 'Your Resident Account Has Been Approved';
            $content  = "Greetings $first_name, <br><br>";
            $content .= "Your resident request has been approved. You can now file for an appointment and request your needed document.";
            DB::table('users')
                ->where('id','=',$request->user_id)
                ->update([
                    'isPendingResident' => 0
                ]);
            Mail::to($user_details[0]->email)
                ->cc(['bc00005rc@gmail.com'])
                ->send(new DynamicMail([
                'subject' => $subject,
                'content' => $content,
                'receiver' => $user_details[0]->email
            ]));
            createAuditLog(session('UserId'),'New User Approved',$request->user_id,'approved');
            return response()->json([
                'msg' => 'New resident has been approved',
                'success' => true
            ]);
        }
        else
        {
            $user_details = DB::table('users')
            ->where('id','=',$request->user_id)
            ->get();
            if(count($user_details)<1)
            {
                return response()->json([
                    'error_msg' => 'New resident request does not exist',
                    'error' => true
                ]);
            }
            $first_name = $user_details[0]->first_name;
            $subject  = 'Your Resident Account Has Been Denied';
            $content  = "Greetings $first_name, <br><br>";
            $content .= "Your resident request has been denied. Please visit the barangay hall to resolve this.";
            createAuditLog(session('UserId'),'New User Denied',$request->user_id,'denied');
            DB::table('users')
                ->where('id','=',$request->user_id)
                ->delete();
            DB::table('supporting_files')
                ->where('user_id','=',$request->user_id)
                ->delete();
            Mail::to($user_details[0]->email)
                ->cc(['bc00005rc@gmail.com'])
                ->send(new DynamicMail([
                'subject' => $subject,
                'content' => $content,
                'receiver' => $user_details[0]->email
            ]));
            return response()->json([
                'msg' => 'New resident has been denied',
                'success' => true
            ]);
        }
    }
    public function importExcelResidents(Request $request)
    {
        try {
            $format = 'd/m/Y';
            $timezone = new DateTimeZone('Asia/Singapore');
            // Load the uploaded file
            $file = $request->file('file_upload');
            $spreadsheet = IOFactory::load($file->getPathname());

            // Get the active sheet
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            $headers = $data[0];
            array_shift($data);
            $object_data = [];
            $existing_emails = explode(',',DB::select("SELECT
            GROUP_CONCAT(email) as mail
            FROM users
            ")[0]->mail);
            $index_val = array_search('email',$headers);
            $data = array_filter($data, function($item) use ($existing_emails,$index_val) {
                return !in_array($item[$index_val], $existing_emails);
            });
            foreach($data as $resident)
            {
                $entry_object = [];
                foreach($headers as $index => $header)
                {
                    if($header == 'birthday')
                    {
                        //return $resident[$index];
                        $date = DateTime::createFromFormat($format, $resident[$index],$timezone);
                        $date = $date->format('Y-m-d');
                        $entry_object[$header] = $date;
                        
                    }
                    else
                    {
                        $entry_object[$header] = $resident[$index];
                    }
                }
                $object_data[] = $entry_object;
            }
            DB::table('users')
            ->insert($object_data);
            return response()->json([
                'msg' => 'Residents have been imported',
                'success' => true
            ]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error loading file: ' . $e->getMessage()]);
        }
    }
}
