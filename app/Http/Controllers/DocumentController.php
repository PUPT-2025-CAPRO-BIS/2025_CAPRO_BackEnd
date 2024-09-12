<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
require_once app_path('Helpers/helpers.php');
class DocumentController extends Controller
{
    public function addDocumentType(Request $request)
    {
        $service = $request->service;
        $price = 0;
        if(!is_null($request->price))
        {
            $price = $request->price;
        }
        $description = $request->description;
        $isCertificate = 1;
        if($request->isCertificate)
        {
            $isCertificate = $request->isCertificate;
        }
        $doc_id = DB::table('document_types')
            ->insertGetId([
                'service' => $service,
                'description' => $description,
                'isCertificate' => $isCertificate,
                'created_at' => date('Y-m-d H:i:s'),
                'price' => $price
            ]);
        /*
        DB::statement("INSERT
        INTO document_types
        (service,description)
        VALUES ('$service','$description')
        ");
        */
        return response()->json([
            'msg' => 'New document type added',
            'success' => true
        ]);
    }
    public function deleteDocumentType(Request $request)
    {
        $document_type_id = $request->document_type_id;
        DB::statement("DELETE
        FROM
        document_types
        WHERE id = '$document_type_id'
        ");
        createAuditLog(session('UserId'),'Document Type Deleted',$document_type_id,'deleted');
        return response()->json([
            'msg' => 'Document type has been deleted',
            'success' => true
        ]);
    }
    public function getDocumentTypes(Request $request)
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
            $search_value = "WHERE service like '%$request->search_value%'";
        }
        $values = DB::select("SELECT
        * 
        FROM document_types
        $search_value
        ORDER BY id
        $item_per_page_limit
        $offset_value
        ");
        $total_pages = DB::select("SELECT
        count(id) as page_count
        FROM document_types
        $search_value
        ORDER BY id
        ")[0]->page_count;

        $total_pages = ceil($total_pages/$item_per_page);
        return response()->json(['data'=>$values,'current_page'=>$page_number,'total_pages'=>$total_pages],200);
    }
    public function updateDocumentTypes(Request $request)
    {
        $update_string = 'SET ';
        $update_string .= !is_null($request->service) ? "service = '$request->service'," : '';
        $update_string .= !is_null($request->isCertificate) ? "isCertificate = '$request->isCertificate'," : '';
        $update_string = rtrim($update_string, ',');
        if(!is_null($request->service) || !is_null($request->isCertificate))
        {
            DB::statement("UPDATE document_types
            $update_string
            where id = '$request->doc_id'
            ");
        }
        if($request->description)
        {
            DB::table('document_types')
                ->where('id','=',$request->doc_id)
                ->update([
                    'description' => $request->description
                ]);
        }
        createAuditLog(session('UserId'),'Document Type Updated',$request->doc_id,'updated');   
        return response()->json([
            'msg' => 'Document edited',
            'success' => true
            
        ],200);
    }
    public function testString(Request $request)
    {

        $string = $request->format;

        // Regular expression pattern to match substrings starting with << and ending with >>
        $pattern = '/<<([^>]+)>>/';

        // Use preg_match_all to find all matches
        preg_match_all($pattern, $string, $matches);
        $sample_user = DB::select("SELECT 
        u.first_name as firstName,
        u.middle_name as middleName,
        u.last_name as lastName,
        ct.civil_status_type as civilStatus
        FROM users as u
        LEFT JOIN civil_status_types as ct on ct.id = u.civil_status_id
        
        ")[0];
        foreach($matches[1] as $match)
        {
            $string = str_replace("<<" . $match . ">>", ((array)$sample_user)[$match], $string);
        }
        return $string;
        return $sample_user;
        $array_version =  ((array)$sample_user);
        return gettype((array)$sample_user);

        return response()->json($matches);
    }
}