<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
//use Spatie\LaravelPdf\Facades\Pdf;
use DB;

class AdminController extends Controller
{
    /*
    public function viewAdminableUsers()
    {
        $adminable_users = DB::select("SELECT
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
        u.id as user_id
        FROM users as u
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        WHERE ur.role_id NOT IN ('2','3')
        ");
        return response()->json($adminable_users,200);
    }
    */
    public function assignRole(Request $request)
    {
        $role_id = $request->role_id;
        $user_id = $request->user_id;
        DB::statement("UPDATE
        user_roles
        set role_id = '$role_id'
        WHERE user_id = '$user_id'
        ");
        return response()->json([
            'msg' => 'user has been assigned new role',
            'success' => true
        ],200);
    }
    public function viewAssignableRoles()
    {
        return DB::select("SELECT
        *
        FROM roles
        WHERE id != '1'
        ");
    }
    public function viewPrivilegedUsers()
    {
        $user_id = session("UserId");
        return DB::select("SELECT
        u.id,
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name
        FROM users as u
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        WHERE ur.role_id IN ('2','3') AND u.id != '$user_id'
        ");
    }
    public function revokeAdminAccess(Request $request)
    {
        $user_id = $request->user_id;
        DB::statement("UPDATE
        user_roles
        set role_id = '1'
        WHERE user_id = '$user_id'
        ");
        return response()->json([
            'msg' => 'Revoked user admin privileges',
            'success' => true
        ]);
    }
    public function dashboardView()
    {
        //Statuses = 0 Ongoing
        //Statuses = 1 Settled
        //Statuses = 2 Unresolved
        //Statuses = 3 Dismissed
        $view = DB::select("SELECT
        count(id) as count_of_residents,
        sum(CASE WHEN male_female = '0' THEN 1 ELSE 0 END) as males,
        sum(CASE WHEN male_female = '1' THEN 1 ELSE 0 END) as females,
        sum(CASE WHEN (DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), birthday )), '%Y') + 0) >= 60 THEN 1 ELSE 0 END ) as count_of_seniors,
        (SELECT count(id) FROM appointments) as schedules,
        (SELECT count(id) FROM blotter_reports WHERE status_resolved = 0) as ongoing,
        (SELECT count(id) FROM blotter_reports WHERE status_resolved = 1) as settled,
        (SELECT count(id) FROM blotter_reports WHERE status_resolved = 2) as unresolved,
        (SELECT count(id) FROM blotter_reports WHERE status_resolved = 3) as dismissed
        FROM users
        WHERE isPendingResident = 0
        ");
        return $view;
    }
    public function uploadIdPicture(Request $request)
    {
        //$path = $request->file('file')->store('uploads', 's3');
        $path = Storage::disk('s3')->put('bis',$request->file('file'));
        //$file = $request->file('image');
        //$path = Storage::disk('s3')->put('bis', file_get_contents($file));;
        //Storage::disk('s3')->putFileAs("bis", $request->file, 'test');
        return 'hi' . env('AWS_ACCESS_KEY_ID');
        return $path;
    }
    public function generateFormatPdf()
    {   
        // Pass any data you need to the view
        $data = [
            'title' => 'Laravel PDF Example',
            'date' => date('m/d/Y')
        ];

        // Load a view file and pass data to it
        $pdf = Pdf::loadView('document.template', $data);
        $pdf->setPaper('A4','portrait');
        // Return the generated PDF to the browser
        return $pdf->download('example.pdf');
    }
    //generateFormatPdf
    public function generatePdf(Request $request)
    {   
        // Pass any data you need to the view
        $download = 0;
        if($request->download)
        {
            $download = $request->download;
        }
        $doc = DB::select("SELECT
        *
        FROM document_types
        WHERE id = '$request->doc_id'
        ")[0];
        $description =$doc->description;
        $title = $doc->service;
        $data = [
            'title' => $title,
            'html_code' => $description
        ];

        // Load a view file and pass data to it
        $pdf = Pdf::loadView('document.template', $data);
        $pdf->setPaper('A4','portrait');
        // Return the generated PDF to the browser
        
        return $download == 1 ? $pdf->download('example.pdf') : $pdf->stream('example.pdf', array("Attachment" => false));
    }

    public function viewAppointmentList(Request $request)
    {
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
            $search_value = "AND (u.first_name like '%$request->search_value%' OR ".
            "u.middle_name like '%$request->search_value%' OR " .
            "u.last_name like '%$request->search_value%' OR " .
            "apt.id = '$request->search_value' OR " .
            "apt.otp_used like '%$request->search_value%'" .
            
            ")";
        }
        $date_filter = '';
        if($request->schedule_date)
        {
            $date_filter = "AND apt.schedule_date = '$request->schedule_date'";
        }
        $appointments = DB::select("SELECT
                apt.id as appointment_id,
                u.id as user_id,
                CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as full_name,
                apt.document_type_id,
                doc_type.service as document_type,
                apt.schedule_date,
                apt.status,
                apt.otp_used,
                apt.purpose,
                apt.created_at as request_date,
                doc_type.price,
                CASE WHEN apt.updated_at IS NULL THEN '' ELSE apt.updated_at END as release_date,
                (
                    SELECT
                    GROUP_CONCAT(appointment_id)
                    FROM supporting_files
                    WHERE appointment_id = apt.id
                    ) as supporting_file_ids
                FROM appointments as apt
                LEFT JOIN users as u on u.id = apt.user_id
                LEFT JOIN document_types as doc_type on doc_type.id = apt.document_type_id
                WHERE apt.id IS NOT NULL
                $search_value
                $date_filter
                ORDER BY apt.id DESC
                $item_per_page_limit
                $offset_value
        ");
        foreach($appointments as $appointment)
        {
            $appointment->supporting_file_ids = explode(',', $appointment->supporting_file_ids);
        }
        $total_pages = DB::select("SELECT
        count(apt.id) as page_count
        FROM appointments as apt
        LEFT JOIN users as u on u.id = apt.user_id
        LEFT JOIN document_types as doc_type on doc_type.id = apt.document_type_id
        WHERE apt.id IS NOT NULL
        $search_value
        $date_filter
        ORDER BY apt.id DESC
        ")[0]->page_count;
        $total_pages = ceil($total_pages/$item_per_page );
        return response()->json([
            'data' => $appointments,'current_page'=>$page_number,'total_pages'=>$total_pages
        ]);
    }
    public function viewSpecificFile(Request $request)
    {
        return DB::select("SELECT base64_file FROM supporting_files WHERE id = $request->supporting_file_id")[0]->base64_file;
    }
    public function viewFileList(Request $request)
    {
        $filter_value = "";
        if($request->appointment_id)
        {
            $filter_value = "WHERE appointment_id = '$request->appointment_id'";
        }
        else if($request->user_id)
        {
            $filter_value = "WHERE user_id = '$request->user_id'";
        }
        else
        {
            return response()->json([
                'error' => true,
                'error_msg' => 'You need to set either the user_id or appointment_id parameter'
            ],401);
        }
        return DB::select("SELECT
                id,
                user_id,
                appointment_id,
                created_at
                FROM supporting_files
                $filter_value
            ");
    }
    public function viewAdminLogs(Request $request)
    {

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
            $search_value = "AND (au.first_name like '%$request->search_value%' OR ".
            "au.middle_name like '%$request->search_value%' OR " .
            "au.last_name like '%$request->search_value%' OR al.log_details like '%$request->search_value%')";
        }

        $data = DB::select("SELECT
            al.id,
            CONCAT(au.first_name, (CASE WHEN au.middle_name = '' THEN '' ELSE ' ' END),au.middle_name,' ',au.last_name) as admin_name,
            al.action_taker_id,
            al.action_type,
            al.action_target_id,
            al.log_details,
            al.created_at
            FROM audit_logs as al
            LEFT JOIN users as au on au.id = al.action_taker_id
            WHERE al.id > 0
            $search_value
            ORDER BY created_at DESC, al.id DESC
            $item_per_page_limit
            $offset_value
        ");

        $total_pages = DB::select("SELECT
        count(al.id) as page_count
        FROM audit_logs as al
        LEFT JOIN users as au on au.id = al.action_taker_id
        WHERE al.id > 0
        $search_value
        ORDER BY al.created_at DESC, al.id DESC
        ")[0]->page_count;
        
        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$data,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
    }
}
