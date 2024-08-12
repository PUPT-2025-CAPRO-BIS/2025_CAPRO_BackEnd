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
        CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
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
        CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name
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
        $view = DB::select("SELECT
        count(id) as count_of_residents,
        sum(CASE WHEN male_female = '0' THEN 1 ELSE 0 END) as males,
        sum(CASE WHEN male_female = '1' THEN 1 ELSE 0 END) as females,
        sum(CASE WHEN (DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), birthday )), '%Y') + 0) >= 60 THEN 1 ELSE 0 END ) as count_of_seniors,
        0 as schedules,
        0 as unresolved,
        0 as ongoing,
        0 as settled,
        0 as dismissed
        FROM users
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
        $date_filter = '';
        if($request->schedule_date)
        {
            $date_filter = "WHERE apt.schedule_date = '$request->schedule_date'";
        }
        $appointments = DB::select("SELECT
                apt.id as appointment_id,
                CONCAT(u.first_name,' ',u.middle_name,' ',u.last_name) as full_name,
                apt.document_type_id,
                doc_type.service as document_type,
                apt.schedule_date,
                apt.status,
                (
                    SELECT
                    GROUP_CONCAT(appointment_id)
                    FROM supporting_files
                    WHERE appointment_id = apt.id
                    ) as supporting_file_ids
                FROM appointments as apt
                LEFT JOIN users as u on u.id = apt.user_id
                LEFT JOIN document_types as doc_type on doc_type.id = apt.document_type_id
                $date_filter
        ");
        foreach($appointments as $appointment)
        {
            $appointment->supporting_file_ids = explode(',', $appointment->supporting_file_ids);
        }
        return response()->json([
            'data' => $appointments,
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
}