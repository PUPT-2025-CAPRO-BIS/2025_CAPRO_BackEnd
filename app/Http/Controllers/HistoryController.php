<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        // Check if both from_date and to_date are provided
        if ($request->from_date && $request->to_date) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $date_filter = "AND apt.schedule_date BETWEEN '$from_date' AND '$to_date'";
        } 
        // If only schedule_date is provided (legacy filter)
        elseif ($request->schedule_date) {
            $date_filter = "AND apt.schedule_date = '$request->schedule_date'";
        }

        // Fetch appointments with filters
        $appointments = DB::select("SELECT
                apt.id as 'No.',
                CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END), u.middle_name, ' ', u.last_name) as 'Name',
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

        // Create and populate spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->setTitle('Appointments Data');

        // Set headers
        if (count($appointments) > 0) {
            $headers = array_keys(get_object_vars($appointments[0]));
            foreach ($headers as $key => $header) {
                $sheet->setCellValue([$key + 1, 1], ucfirst($header));
            }
        }

        // Populate rows with appointment data
        $row = 2;
        foreach ($appointments as $appointment) {
            $col = 1;
            foreach ($appointment as $value) {
                $sheet->setCellValue([$col, $row], $value);
                $col++;
            }
            $row++;
        }

        // Prepare Excel file for download
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

        // Initialize filters
        $search_value = '';
        $category_filter = '';
        $date_filter = '';

        // Search filter
        if ($request->search_value) {
            $search_value = "AND (br.complainee_name LIKE '%$request->search_value%' OR ".
                            "br.complainant_name LIKE '%$request->search_value%' OR ".
                            "au.first_name LIKE '%$request->search_value%' OR ".
                            "au.middle_name LIKE '%$request->search_value%' OR ".
                            "au.last_name LIKE '%$request->search_value%')";
        }

        // Date range filter
        if ($request->from_date && $request->to_date) {
            $from_date = $request->from_date . ' 00:00:00';  // Ensure start of the day
            $to_date = $request->to_date . ' 23:59:59';      // Ensure end of the day
            $date_filter = "AND br.created_at BETWEEN '$from_date' AND '$to_date'";
        }

        // Category filter (handle NULL values in category)
        if ($request->category) {
            $category = $request->category;
            $category_filter = "AND (br.category = '$category' OR br.category IS NULL)";
        }

        // Execute the query with dynamic filters, including category
        $blotters = DB::select("
            SELECT
                br.id AS 'No.',
                CASE 
                    WHEN br.complainee_name IS NULL 
                    THEN CONCAT(ceu.first_name, ' ', ceu.middle_name, ' ', ceu.last_name) 
                    ELSE br.complainee_name 
                END AS Complainee,
                br.complaint_remarks AS Remarks,
                CASE 
                    WHEN br.status_resolved = 0 THEN 'Ongoing'
                    WHEN br.status_resolved = 1 THEN 'Settled'
                    WHEN br.status_resolved = 2 THEN 'Unresolved'
                    WHEN br.status_resolved = 3 THEN 'Dismissed'
                END AS Status,
                br.created_at AS 'Requested On',
                CASE 
                    WHEN br.complainant_name IS NULL 
                    THEN CONCAT(cau.first_name, ' ', cau.middle_name, ' ', cau.last_name) 
                    ELSE br.complainant_name 
                END AS Complainant,
                CONCAT(cu.first_name, ' ', cu.middle_name, ' ', cu.last_name) AS 'Admin Name',
                br.category AS Category
            FROM blotter_reports AS br
            LEFT JOIN users AS cu ON cu.id = br.admin_id
            LEFT JOIN users AS ceu ON ceu.id = br.complainee_id
            LEFT JOIN users AS cau ON cau.id = br.complainant_id
            WHERE br.id IS NOT NULL
            $search_value
            $date_filter
            $category_filter
            ORDER BY br.id DESC
        ");

        // If no blotters found, return a message (for debugging)
        if (empty($blotters)) {
            return response()->json(['message' => 'No blotters found for the given criteria'], 404);
        }

        // Create and populate spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Adjust columns to auto-size
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Blotters Data');

        // Set headers, including the 'Category' column
        if (count($blotters) > 0) {
            $headers = array_keys(get_object_vars($blotters[0]));
            foreach ($headers as $key => $header) {
                // Convert numeric index to Excel-style column (A, B, C, ...)
                $column = Coordinate::stringFromColumnIndex($key + 1);
                $sheet->setCellValue($column . '1', ucfirst($header));  // Add 'Category' as a header
            }
        }

        // Populate rows with blotter data, including the 'Category' column
        $row = 2; // Starting from the second row
        foreach ($blotters as $blotter) {
            $col = 1;
            foreach ($blotter as $value) {
                $column = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($column . $row, $value);
                $col++;
            }
            $row++;
        }

        // Prepare Excel file for download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Blotters-Reports.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
    public function downloadUsers(Request $request)
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
        $search_value = '';
        if($request->search_value)
        {
            $search_value = "AND (first_name like '%$request->search_value%' OR ".
            "middle_name like '%$request->search_value%' OR " .
            "last_name like '%$request->search_value%')";
        }


        $dbq = '"';
        $users = DB::select("SELECT
        u.id as 'No.',
        CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as 'Name',
        u.Email as ' Email',
        ct.civil_status_type as 'Civil Status',
        CASE WHEN u.male_female = 0 THEN 'Male' ELSE 'Female' END as 'Gender',
        u.birthday as 'Birthday',
        u.cell_number as 'Cellphone No.',
        CASE WHEN u.voter_status = 0 THEN 'No' ELSE 'Yes' END as 'Is Voter?',
        u.current_address as 'Address',
        CASE WHEN u.isPendingResident = 0 THEN 'Yes' ELSE 'No' END as 'Is Pending Approval',
        DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS Age
        FROM(
        SELECT *
        FROM users
        WHERE id != '$user_id'
        $search_value
        ORDER BY isPendingResident DESC,id ASC
        ) as u
        LEFT JOIN barangay_officials as bo on bo.user_id = u.id
        LEFT JOIN user_roles as ur on ur.user_id = u.id
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
        ");
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        // Set the sheet title
        $sheet->setTitle('Users Data');

        // Get the headers from the first item in the collection
        $headers = array_keys(get_object_vars($users[0]));
        // Populate headers
        foreach ($headers as $key => $header) {
            $sheet->setCellValue([$key + 1, 1], ucfirst($header));
        }
        // Populate the spreadsheet with the collection data
        $row = 2; // Starting from the second row (first row for headers)
        foreach ($users as $user) {
            $col = 1;
            foreach ($user as $value) {
                $sheet->setCellValue([$col, $row], $value);
                $col++;
            }
            $row++;
        }
        // Write the file to a temporary location
        $writer = new Xlsx($spreadsheet);
        
        $fileName = 'User-List.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
        return $users;
        //return response()->json($users,200);
    }
}
