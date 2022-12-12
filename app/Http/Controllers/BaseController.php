<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($success = true, $message = '', $data = [], $code = 200)
    {
        
        // $object = new stdClass();
        // foreach ($data as $key => $value)
        // {
        //     $data[$key] = (object) $value;
        //     foreach ($value as $k => $val) {
        //         $object->$k = $val;
        //     }
        // }

    	$response = [
            'success' => $success,
            'code'    => $code, 
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($response, 200);
    }
}
