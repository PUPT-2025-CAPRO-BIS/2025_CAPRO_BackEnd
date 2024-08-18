<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppointmentController extends Controller
{
    public function approveAppointment(Request $request)
    {
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
            ->update([
                'status' => 'Approved'
            ])
            ->where('id','=',$request->appointment_id);
        return response()->json([
            'msg' => 'Appointment has been approved',
            'success' => true 
        ],200);
    }
    public function rejectAppointment(Request $request)
    {
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
            ->update([
                'status' => 'Rejected'
            ])
            ->where('id','=',$request->appointment_id);
        return response()->json([
            'msg' => 'Appointment has been rejected',
            'success' => true 
        ],200);
    }
    public function releaseAppointmentDocument(Request $request)
    {
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
        elseif($appointment[0]->status != 'Approved')
        {
            return response()->json([
                'error_msg' => 'Appointment has not been approved',
                'success' => false,
                'error' => true
            ],200);
        }
        DB::table('appointments')
            ->update([
                'status' => 'Released'
            ])
            ->where('id','=',$request->appointment_id);
        return response()->json([
            'msg' => 'Appointment has been rejected',
            'success' => true 
        ],200);
    }
    public function downloadReleasedDocument(Request $request)
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
}