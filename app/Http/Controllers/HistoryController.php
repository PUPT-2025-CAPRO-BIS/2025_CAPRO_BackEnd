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
        $from_date = '';
        $to_date = '';
    
        // Check if both from_date and to_date are provided
        if ($request->from_date && $request->to_date) {
            // Use provided date range
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $date_filter = "AND apt.schedule_date BETWEEN '$from_date' AND '$to_date'";
        } else {
            // Get the oldest and latest schedule dates from the appointments table if no date range is provided
            $dateRange = DB::select("
                SELECT 
                    MIN(apt.schedule_date) AS oldest_date, 
                    MAX(apt.schedule_date) AS latest_date 
                FROM appointments AS apt
                LEFT JOIN users AS u ON u.id = apt.user_id
                LEFT JOIN document_types AS doc_type ON doc_type.id = apt.document_type_id
                WHERE apt.id IS NOT NULL
                $search_value
            ");
            
            if ($dateRange && count($dateRange) > 0) {
                $from_date = date('Y-m-d', strtotime($dateRange[0]->oldest_date));
                $to_date = date('Y-m-d', strtotime($dateRange[0]->latest_date));
                // Adjust filter based on the actual oldest and latest dates
                $date_filter = "AND apt.schedule_date BETWEEN '$from_date' AND '$to_date'";
            } else {
                // Handle case where no records are found and no date range can be determined
                $from_date = 'N/A';
                $to_date = 'N/A';
            }
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
    
        // Determine the maximum number of columns (based on the headers)
        $maxColumn = count($appointments) > 0 ? count(array_keys(get_object_vars($appointments[0]))) : 8; // Assuming at least 8 columns
    
        // Merge cells for "Appointment Report" and set the title
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '1');
        $sheet->setCellValue('A1', 'Appointment Report');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Merge cells for Date Range and set the date range
        $sheet->mergeCells('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '2');
        if ($from_date !== 'N/A' && $to_date !== 'N/A') {
            $sheet->setCellValue('A2', "Date Range: $from_date to $to_date");
        } else {
            $sheet->setCellValue('A2', "Date Range: Not Specified");
        }
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Set headers (starting from row 3)
        if (count($appointments) > 0) {
            $headers = array_keys(get_object_vars($appointments[0]));
            foreach ($headers as $key => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($key + 1);
                $sheet->setCellValue($columnLetter . '3', ucfirst($header));
                $sheet->getStyle($columnLetter . '3')->getFont()->setBold(true);
            }
        }
    
        // Populate rows with appointment data (starting from row 4)
        $row = 4;
        foreach ($appointments as $appointment) {
            $col = 1;
            foreach ($appointment as $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }
    
        // Prepare Excel file for download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Appointment Report.xlsx';
    
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
        $from_date = '';
        $to_date = '';
    
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
            // Use provided date range
            $from_date = date('Y-m-d', strtotime($request->from_date)); // Extract only date
            $to_date = date('Y-m-d', strtotime($request->to_date));     // Extract only date
            $date_filter = "AND br.created_at BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
        } else {
            // Get the oldest and latest dates from the blotters table if no date range is provided
            $dateRange = DB::select("
                SELECT 
                    MIN(br.created_at) AS oldest_date, 
                    MAX(br.created_at) AS latest_date 
                FROM blotter_reports AS br
                WHERE br.id IS NOT NULL
                $search_value
                $category_filter
            ");
            
            if ($dateRange && count($dateRange) > 0) {
                $from_date = date('Y-m-d', strtotime($dateRange[0]->oldest_date));
                $to_date = date('Y-m-d', strtotime($dateRange[0]->latest_date));
                // Adjust filter based on the actual oldest and latest dates
                $date_filter = "AND br.created_at BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
            } else {
                // Handle case where no records are found and no date range can be determined
                $from_date = 'N/A';
                $to_date = 'N/A';
            }
        }
    
        // Category filter
        if ($request->category) {
            $category = $request->category;
    
            // If "Others" is selected, get entries not matching specified categories
            if ($category === "Others") {
                $category_filter = "AND br.category NOT IN ('Assault', 'Verbal Abuse', 
                    'Theft', 'Domestic Violence', 'Vandalism', 'Trespassing', 
                    'Public Disturbance', 'Disorderly Conduct', 'Child Welfare Concern', 
                    'Harassment', 'Property Conflict', 'Neighbor Conflict')";
            } else {
                $category_filter = "AND br.category = '$category'";
            }
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
    
        // Determine the maximum number of columns
        $maxColumn = count($blotters) > 0 ? count(array_keys(get_object_vars($blotters[0]))) : 8; // Assuming at least 8 columns
    
        // Merge cells for "Blotter Report" and set the title
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '1');
        $sheet->setCellValue('A1', 'Blotter Report');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Set the date range in the header based on the calculated or provided dates
        $sheet->mergeCells('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '2');
        if ($from_date !== 'N/A' && $to_date !== 'N/A') {
            $sheet->setCellValue('A2', "Date Range: $from_date to $to_date");
            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
    
        // Set headers (starting from row 3)
        if (count($blotters) > 0) {
            $headers = array_keys(get_object_vars($blotters[0]));
            foreach ($headers as $key => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($key + 1);
                $sheet->setCellValue($columnLetter . '3', ucfirst($header));
                $sheet->getStyle($columnLetter . '3')->getFont()->setBold(true);
            }
        }
    
        // Populate rows with blotter data (starting from row 4)
        $row = 4;
        foreach ($blotters as $blotter) {
            $col = 1;
            foreach ($blotter as $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }
    
        // Prepare Excel file for download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Blotter Report.xlsx';
    
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
    
        // Initialize search value
        $search_value = '';
        if ($request->search_value) {
            $search_value = "AND (first_name like '%$request->search_value%' OR ".
                            "middle_name like '%$request->search_value%' OR " .
                            "last_name like '%$request->search_value%')";
        }
    
        // Filter by `isPendingResident = 0` (approved residents only)
        $users = DB::select("SELECT
            u.id as 'No.',
            CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END),u.middle_name,' ',u.last_name) as 'Name',
            u.Email as 'Email',
            ct.civil_status_type as 'Civil Status',
            CASE WHEN u.male_female = 0 THEN 'Male' ELSE 'Female' END as 'Gender',
            u.birthday as 'Birthday',
            CASE WHEN u.voter_status = 0 THEN 'No' ELSE 'Yes' END as 'Is Voter?',
            DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS Age,
            u.block as 'Block',
            u.lot as 'Lot',
            u.purok as 'Purok',
            u.street as 'Street',
            u.household as 'Household',
            u.house_and_lot_ownership as 'House and Lot Ownership',
            u.living_with_owner as 'Living With Owner',
            u.renting as 'Renting',
            u.relationship_to_owner as 'Relationship to Owner',
            u.pet_details as 'Pet Details',
            u.pet_vaccination as 'Pet Vaccination'
            FROM users u
            LEFT JOIN civil_status_types ct ON ct.id = u.civil_status_id
            WHERE u.id != '$user_id' 
            AND u.isPendingResident = 0 
            $search_value
            ORDER BY u.id ASC
        ");
    
        // Create and populate the spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set column auto-sizing
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        // Set the sheet title
        $sheet->setTitle('Residents Report');
    
        // Merge cells for "Users Report" and set the title
        $maxColumn = count($users) > 0 ? count(array_keys(get_object_vars($users[0]))) : 8; // Assuming at least 8 columns
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '1');
        $sheet->setCellValue('A1', 'Residents Report');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Set headers (starting from row 2)
        if (count($users) > 0) {
            $headers = array_keys(get_object_vars($users[0]));
            foreach ($headers as $key => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($key + 1);
                $sheet->setCellValue($columnLetter . '2', ucfirst($header));
                $sheet->getStyle($columnLetter . '2')->getFont()->setBold(true);
            }
        }
    
        // Populate the spreadsheet with user data (starting from row 3)
        $row = 3;
        foreach ($users as $user) {
            $col = 1;
            foreach ($user as $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }
    
        // Write the file to a temporary location
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Residents Report.xlsx';
    
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        return $response;
    }
    public function downloadPendingResidents(Request $request)
    {
        $user_id = session("UserId");
    
        // Initialize search value
        $search_value = '';
        if ($request->search_value) {
            $search_value = "AND (first_name like '%$request->search_value%' OR ".
                            "middle_name like '%$request->search_value%' OR " .
                            "last_name like '%$request->search_value%')";
        }
    
        // Filter by `isPendingResident = 1` (pending residents only)
        $pendingResidents = DB::select("SELECT
            u.id as 'No.',
            CONCAT(u.first_name, (CASE WHEN u.middle_name = '' THEN '' ELSE ' ' END), u.middle_name, ' ', u.last_name) as 'Name',
            u.Email as 'Email',
            ct.civil_status_type as 'Civil Status',
            CASE WHEN u.male_female = 0 THEN 'Male' ELSE 'Female' END as 'Gender',
            u.birthday as 'Birthday',
            CASE WHEN u.voter_status = 0 THEN 'No' ELSE 'Yes' END as 'Is Voter?',
            DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), u.birthday )), '%Y') + 0 AS Age,
            u.block as 'Block',
            u.lot as 'Lot',
            u.purok as 'Purok',
            u.street as 'Street',
            u.household as 'Household',
            u.house_and_lot_ownership as 'House and Lot Ownership',
            u.living_with_owner as 'Living With Owner',
            u.renting as 'Renting',
            u.relationship_to_owner as 'Relationship to Owner',
            u.pet_details as 'Pet Details',
            u.pet_vaccination as 'Pet Vaccination'
            FROM users u
            LEFT JOIN civil_status_types ct ON ct.id = u.civil_status_id
            WHERE u.id != '$user_id' 
            AND u.isPendingResident = 1
            $search_value
            ORDER BY u.id ASC
        ");
    
        // Create and populate the spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set column auto-sizing
        foreach (range('A', 'Z') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        // Set the sheet title
        $sheet->setTitle('Pending Residents Report');
    
        // Merge cells for "Pending Residents Report" and set the title
        $maxColumn = count($pendingResidents) > 0 ? count(array_keys(get_object_vars($pendingResidents[0]))) : 8; // Assuming at least 8 columns
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColumn) . '1');
        $sheet->setCellValue('A1', 'Pending Residents Report');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Set headers (starting from row 2)
        if (count($pendingResidents) > 0) {
            $headers = array_keys(get_object_vars($pendingResidents[0]));
            foreach ($headers as $key => $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($key + 1);
                $sheet->setCellValue($columnLetter . '2', ucfirst($header));
                $sheet->getStyle($columnLetter . '2')->getFont()->setBold(true);
            }
        }
    
        // Populate the spreadsheet with pending resident data (starting from row 3)
        $row = 3;
        foreach ($pendingResidents as $resident) {
            $col = 1;
            foreach ($resident as $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($columnLetter . $row, $value);
                $col++;
            }
            $row++;
        }
    
        // Write the file to a temporary location
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Pending Residents Report.xlsx';
    
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });
    
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        return $response;
    }
}
