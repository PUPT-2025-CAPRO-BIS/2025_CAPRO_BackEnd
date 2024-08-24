<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use DateTime;
use Barryvdh\DomPDF\Facade\Pdf;
class AppointmentController extends Controller
{
    public function approveOrRejectAppointment(Request $request)
    {
        $status = $request->approve_reject == 0 ? 'Approved' : 'Rejected';
        $appointment = DB::table('appointments')
        ->where('id','=',$request->appointment_id)
        ->get();
        if(count($appointment) < 1)
        {
            return response()->json([
                'error_msg' => 'Appointment does not exist',
                'success' => false,
                'error' => true
            ],200);
        }
        DB::table('appointments')
            ->where('id','=',$request->appointment_id)
            ->update([
                'status' => $status
            ]);
        return response()->json([
            'msg' => "Appointment Has Been $status",
            'success' => true 
        ],200);
    }
    public function downloadAndReleaseDocument(Request $request)
    {
            // Pass any data you need to the view
            $download = 0;
            if($request->download)
            {
                $download = $request->download;
            }
            $appointment_deets = DB::table('appointments')
            ->where('id','=',$request->appointment_id)
            ->get();
            if(count($appointment_deets)< 1)
            {
                return response()->json([
                    'error_msg' => 'This appointment does not exist',
                    'error' => true
                ]);
            }
            /*
            if($appointment_deets[0]->status != 'Approved')
            {
                return response()->json([
                    'error_msg' => 'This appointment was not approved',
                    'error' => true
                ]);
            }
                */
            DB::table('appointments')
                ->where('id','=',$request->appointment_id)
                ->update([
                    'status' => 'Released'
                ]);
            $appointment_deets = $appointment_deets[0];
            $doc = DB::select("SELECT
            *
            FROM document_types
            WHERE id = '$appointment_deets->document_type_id'
            ")[0];
            $description =$doc->description;
            $title = $doc->service;
            $user_deets = DB::table('users')
                ->where('id','=',$appointment_deets->user_id)
                ->get()[0];
            $params = [];
            foreach($user_deets as $field => $value)
            {
                if(!is_null($value))
                {
                    if($field == 'birthday')
                    {
                        $value = new DateTime($value);
                        $format = 'F d, Y';
                        $value = $value->format($format);
                    }
                    $description = str_replace('$' . $field, $value, $description);
                }
            }
            $description = str_replace('$name', 
                $user_deets->first_name . ' '  .
                ($user_deets->middle_name == '' || is_null($user_deets->middle_name) ? '' : $user_deets->middle_name . ' ') .
                $user_deets->last_name
                , $description);
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
}