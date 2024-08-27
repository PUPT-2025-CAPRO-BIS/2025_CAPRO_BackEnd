<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DB;

class HistoryController extends Controller
{
    
    public function downloadAppointments(Request $request)
    {
        $search_value = '';
        if($request->search_value)
        {
            $search_value = "AND (u.first_name like '%$request->search_value%' OR ".
            "u.middle_name like '%$request->search_value%' OR " .
            "u.last_name like '%$request->search_value%' OR " .
            "apt.otp_used like '%$request->search_value%'" .
            
            ")";
        }
        $date_filter = '';
        if($request->schedule_date)
        {
            $date_filter = "AND apt.schedule_date = '$request->schedule_date'";
        }
        $appointments = DB::select("SELECT
                apt.id as 'No.',
                CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as 'Name',
                doc_type.service as 'Document Type',
                apt.schedule_date as 'Scheduled Date',
                apt.status,
                apt.purpose,
                apt.created_at as 'Request Date',
                apt.updated_at as 'Release Date',
                doc_type.Price
                FROM appointments as apt
                LEFT JOIN users as u on u.id = apt.user_id
                LEFT JOIN document_types as doc_type on doc_type.id = apt.document_type_id
                WHERE apt.id IS NOT NULL
                $search_value
                $date_filter
                ORDER BY apt.id DESC
        ");


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // Set the sheet title
        $sheet->setTitle('Users Data');

        // Get the headers from the first item in the collection
        $headers = array_keys(get_object_vars($appointments[0]));
        // Populate headers
        foreach ($headers as $key => $header) {
            $sheet->setCellValue([$key + 1, 1], ucfirst($header));
        }
        // Populate the spreadsheet with the collection data
        $row = 2; // Starting from the second row (first row for headers)
        foreach ($appointments as $appointment) {
            $col = 1;
            foreach ($appointment as $value) {
                $sheet->setCellValue([$col, $row], $value);
                $col++;
            }
            $row++;
        }
        // Write the file to a temporary location
        $writer = new Xlsx($spreadsheet);
        
        $fileName = 'Appointments.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
    public function downloadBlotters(Request $request)
    {
        $user_id = session("UserId");
        $offset = 0;
        $search_value = '';
        if($request->search_value)
        {
            $search_value = 
            "WHERE
            complainee_name like '%$request->search_value%' OR ".

            "cu.first_name like '%$request->search_value%' OR ".
            "cu.middle_name like '%$request->search_value%' OR " .
            "cu.last_name like '%$request->search_value%' OR " .

            "au.first_name like '%$request->search_value%' OR ".
            "au.middle_name like '%$request->search_value%' OR " .
            "au.last_name like '%$request->search_value%'" ;
        }



        $blotters = DB::select("SELECT
        br.id as 'No.',
        br.complainee_name as 'Complainee',
        br.complaint_remarks as Remarks,
        CASE WHEN br.status_resolved = 0 THEN 'Ongoing' ELSE 'Resolved' END as Status,
        br.created_at as 'Requested On',
        CONCAT(cu.first_name, (CASE WHEN cu.middle_name = '' THEN '' ELSE ' ' END),cu.middle_name,' ',cu.last_name) as 'Complainant Name',
        CONCAT(cu.first_name, (CASE WHEN cu.middle_name = '' THEN '' ELSE ' ' END),cu.middle_name,' ',cu.last_name) as 'Admin Name'
        FROM(
        SELECT *
        FROM blotter_reports
        ) as br
        LEFT JOIN users as cu on cu.id = br.complainant_id
        LEFT JOIN users as au on au.id = br.admin_id
        $search_value
        ORDER BY br.id
        ");


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // Set the sheet title
        $sheet->setTitle('Users Data');

        // Get the headers from the first item in the collection
        $headers = array_keys(get_object_vars($blotters[0]));
        // Populate headers
        foreach ($headers as $key => $header) {
            $sheet->setCellValue([$key + 1, 1], ucfirst($header));
        }
        // Populate the spreadsheet with the collection data
        $row = 2; // Starting from the second row (first row for headers)
        foreach ($blotters as $blotter) {
            $col = 1;
            foreach ($blotter as $value) {
                $sheet->setCellValue([$col, $row], $value);
                $col++;
            }
            $row++;
        }
        // Write the file to a temporary location
        $writer = new Xlsx($spreadsheet);

        $fileName = 'Blotter-Reports.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}