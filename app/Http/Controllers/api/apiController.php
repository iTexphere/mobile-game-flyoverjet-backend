<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\RegisterDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class apiController extends Controller
{
    public function macReverse($data)
    {
        $mac = sha1(strrev($data . 'xsasdasd238@3$**(_+^#%$^'));
        return $mac;
    }

    public function getInitialStatus(Request $request)
    {
        $res = [];
        $macAddress = $request->get('mac-address');

        if (empty($macAddress)) {
            return response()->json('record not found!', 400);
        }
        $data = RegisterDetails::where('macAddress', $macAddress)->select('timestamp')->first();
        if (!empty($data)) {
            $token = $this->macReverse($macAddress);
            $to = Carbon::parse($data->timestamp);
            $from = Carbon::now();
            $timeDiff = $to->diffInMinutes($from);
            if (($timeDiff / 60) > 24) {
                $res = [
                    'winningPoints' => mt_rand(50, 100),
                    'winningAmmount' => mt_rand(50, 100),
                    'maxTime' => true,
                    'token' => $token
                ];
            } else {
                $res = [
                    'winningPoints' => mt_rand(50, 100),
                    'winningAmmount' => mt_rand(50, 100),
                    'maxTime' => false,
                    'token' => $token
                ];
            }
            return response()->json($res, 200);
        } else {
            return response()->json('Record not found', 400);
        }
    }


    public function record(Request $request)
    {

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'mobileNumber' => $request->mobileNumber,
            'macAddress' => $request->deviceInfo['mac-address'],
            'deviceId' => $request->deviceInfo['deivceId'],
            'imiNumber' =>  $request->deviceInfo['imiNumber'],
            'timestamp' => Carbon::now()
        ];

        ######################## Validate Data ########################
        if (is_array($request->deviceInfo['imiNumber'])) {
            $rules = [
                'name' => 'required',
                'mobileNumber' => 'required',
                'macAddress' => 'required',
                'deviceId' => 'required',
                'imiNumber' => 'required|array',
                'imiNumber.*' => 'required|string|distinct',
            ];
        } else {
            $rules = [
                'name' => 'required',
                'mobileNumber' => 'required',
                'macAddress' => 'required',
                'deviceId' => 'required',
                'imiNumber' => 'required',
            ];
        }

        // $messages = [
        //     'name.required' => 'Please enter a name.',
        //     'mobileNumber.required' => 'Please enter a mobile number.',
        //     'macAddress.required' => 'MAC Address not found.',
        //     'deviceId.required' => 'Device Id not found.',
        //     'imiNumber.required' => 'IMI Number not found.'
        // ];
        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json($validate->messages(), 200);
        }
        ######################## Validate End ########################


        ######################## Insert Data ########################
        $data['imiNumber'] = json_encode($data['imiNumber']);
        $response_data = RegisterDetails::create($data);

        $response_data = [
            'id' => $response_data->id,
            'name' => $response_data->name,
            'email' => $response_data->email,
            "mobileNumber" => $response_data->mobileNumber,
            "deviceInfo" => [
                "mac-address" => $response_data->macAddress,
                "deivceId" => $response_data->deviceId,
                "imiNumber" =>json_decode($response_data->imiNumber),
            ]
        ];
        return response()->json($response_data, 201);
    }

    ############ if you want get mac address using php ################
    // public function MAC()
    // {
    //     ob_start();
    //     system('ipconfig/all');
    //     $mycom = ob_get_contents();
    //     ob_clean();
    //     $findme = "Physical";
    //     $pmac = strpos($mycom, $findme);
    //     $mac = substr($mycom, ($pmac + 36), 17);
    //     return $mac;
    // }
}
