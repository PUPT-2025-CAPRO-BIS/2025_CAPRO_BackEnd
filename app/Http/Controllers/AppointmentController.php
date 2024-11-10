<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use DateTime;
use Barryvdh\DomPDF\Facade\Pdf;
require_once app_path('Helpers/helpers.php');
class AppointmentController extends Controller
{
    public function approveOrRejectAppointment(Request $request)
    {
        $status = $request->approve_reject == 0 ? 'Approved' : 'Rejected';
        $appointment = DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'error_msg' => 'Appointment does not exist',
                'success' => false,
                'error' => true
            ], 200);
        }

        DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->update([
                'status' => $status
            ]);

        // Ensure payment_status is not null
        $payment_status = $appointment->payment_status ? $appointment->payment_status : 'Unpaid';

        return response()->json([
            'msg' => "Appointment Has Been $status",
            'status' => $status,
            'payment_status' => $payment_status, // Return the payment status with default 'Unpaid' if null
            'success' => true
        ], 200);
    }
    public function markAsPaid(Request $request)
    {
        // Fetch the appointment by ID
        $appointment = DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->first(); // Changed to first() to fetch only one record

        // Check if the appointment exists
        if (!$appointment) {
            return response()->json([
                'error_msg' => 'Appointment does not exist',
                'success' => false,
                'error' => true
            ], 200);
        }

        // Update the payment_status to 'Paid'
        DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->update([
                'payment_status' => 'Paid', // Set the status to 'Paid'
                'updated_at' => now() // Update the timestamp
            ]);

        // Create an audit log for the action
        createAuditLog(session('UserId'), 'Appointment marked as Paid', $request->appointment_id, 'paid');

        // Respond with success and return the updated status and payment_status
        return response()->json([
            'msg' => 'Appointment has been marked as Paid',
            'payment_status' => 'Paid',  // Returning updated payment_status
            'status' => $appointment->status, // Return the status if needed
            'success' => true
        ], 200);
    }
    public function downloadAndReleaseDocument(Request $request)
    {
        // Pass any data you need to the view
        $download = 0;
        if ($request->download) {
            $download = $request->download;
        }
        $appointment_deets = DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->get();
        if (count($appointment_deets) < 1) {
            return response()->json([
                'error_msg' => 'This appointment does not exist',
                'error' => true
            ]);
        }
        DB::table('appointments')
            ->where('id', '=', $request->appointment_id)
            ->update(['status' => 'Released']);
        
        if ($request->user_id) {
            createAuditLog($request->user_id, 'Appointment Released', $request->appointment_id, 'released');
        }
        
        if (is_null($appointment_deets[0]->updated_at)) {
            DB::table('appointments')
                ->where('id', '=', $request->appointment_id)
                ->update(['updated_at' => date('Y-m-d H:i:s')]);
        }
    
        $appointment_deets = $appointment_deets[0];
        $doc = DB::select("SELECT * FROM document_types WHERE id = '$appointment_deets->document_type_id'")[0];
        $description = $doc->description;
        $title = $doc->service;
        $user_deets = DB::table('users')
            ->where('id', '=', $appointment_deets->user_id)
            ->get()[0];
        
        // Fetch civil_status_id directly and use it to determine the civil status
        $civil_status_id = $user_deets->civil_status_id;
        $gender = $user_deets->male_female;
    
        // Determine honorific (Mr., Ms., Mrs., etc.)
        $honorifics = '';
        if ($gender === 0) { // Male
            $honorifics = 'Mr.';
        } elseif ($gender === 1) { // Female
            // For civil status, check if married (use 'Mrs.') or not (use 'Ms.')
            if ($civil_status_id == 2) { // Married
                $honorifics = 'Mrs.';
            } else { // Single, Widowed, Separated
                $honorifics = 'Ms.';
            }
        }
    
        // Determine gender pronoun (his/her)
        $gender_pronoun = '';
        if ($gender === 0) { // Male
            $gender_pronoun = '<u>his</u>/her'; // Underline "his"
        } elseif ($gender === 1) { // Female
            $gender_pronoun = 'his/<u>her</u>'; // Underline "her"
        }
    
        // Replace the gender pronoun in the description
        $description = str_replace('$gender_pronoun', $gender_pronoun, $description);
    
        // Get today's date and add ordinal suffix directly to the day
        $currentDate = new DateTime();
        $month = $currentDate->format('F');
        $day = $currentDate->format('j');
        if (!in_array(($day % 100), [11, 12, 13])) {
            switch ($day % 10) {
                case 1: $day .= 'st'; break;
                case 2: $day .= 'nd'; break;
                case 3: $day .= 'rd'; break;
                default: $day .= 'th'; break;
            }
        } else {
            $day .= 'th';
        }
        $year = $currentDate->format('Y');
    
        // Update the description with today's date placeholders
        $description = str_replace('$month', $month, $description);
        $description = str_replace('$day', $day, $description);
        $description = str_replace('$year', $year, $description);
    
        // Combine block, lot, purok, and street into a full address
        $address = '';
        if (!is_null($user_deets->block)) {
            $address .= $user_deets->block . ', ';
        }
        if (!is_null($user_deets->lot)) {
            $address .= $user_deets->lot . ', ';
        }
        if (!is_null($user_deets->purok)) {
            $address .= $user_deets->purok . ', ';
        }
        if (!is_null($user_deets->street)) {
            $address .= $user_deets->street;
        }
        $address = rtrim($address, ', ');
    
        // Replace user details in the description
        foreach ($user_deets as $field => $value) {
            if (!is_null($value)) {
                if ($field == 'birthday') {
                    $value = (new DateTime($value))->format('F d, Y');
                }
                if ($field == 'male_female') {
                    $field = 'gender';
                    $value = $value == 0 ? 'male' : 'female';
                }
                $description = str_replace('$' . $field, $value, $description);
            }
        }
    
        // Replace the full name with honorifics
        $description = str_replace('$honorifics', $honorifics, $description);
        $description = str_replace('$name',
            $user_deets->first_name . ' ' .
            ($user_deets->middle_name == '' || is_null($user_deets->middle_name) ? '' : $user_deets->middle_name . ' ') .
            $user_deets->last_name,
            $description
        );
    
        // Replace the address
        $description = str_replace('$address', $address, $description);
    
        // Handle civil status layout based on civil_status_id (1=Single, 2=Married, 3=Widowed, 4=Separated)
        $civil_status_layout = '';
    
        switch ($civil_status_id) {
            case 1:
                $civil_status_layout = '<u>single</u>/married/widowed/separated';
                break;
            case 2:
                $civil_status_layout = 'single/<u>married</u>/widowed/separated';
                break;
            case 3:
                $civil_status_layout = 'single/married/<u>widowed</u>/separated';
                break;
            case 4:
                $civil_status_layout = 'single/married/widowed/<u>separated</u>';
                break;
            default:
                $civil_status_layout = 'single/married/widowed/separated'; // Default layout
                break;
        }
    
        $description = str_replace('$civil_status', $civil_status_layout, $description);
    
        // Handle house and lot ownership layout with underlining
        $house_and_lot_status = '';
        if ($user_deets->house_and_lot_ownership === 'Yes') {
            $house_and_lot_status = 'homeowner';
        } elseif ($user_deets->house_and_lot_ownership === 'No') {
            $house_and_lot_status = 'tenant';
        }
        $house_and_lot_options = ['tenant', 'homeowner'];
        $house_and_lot_layout = '';
    
        // Create the layout for house and lot ownership, underlining the correct one
        foreach ($house_and_lot_options as $option) {
            if ($option === $house_and_lot_status) {
                $house_and_lot_layout .= "<u>$option</u>";
            } else {
                $house_and_lot_layout .= $option;
            }
            if ($option !== end($house_and_lot_options)) {
                $house_and_lot_layout .= '/';
            }
        }
        $description = str_replace('$house_and_lot_status', $house_and_lot_layout, $description);
    
        $data = [
            'title' => $title,
            'html_code' => $description
        ];
    
        // Load a view file and pass data to it
        $pdf = Pdf::loadView('document.template', $data);
        $pdf->setPaper('A4', 'portrait');
    
        // Return the generated PDF to the browser
        return $download == 1 ? $pdf->download('example.pdf') : $pdf->stream('example.pdf', ["Attachment" => false]);
    }
}    
