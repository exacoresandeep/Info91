<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use DB;
use DateTime;
use JWTAuth;
use App\Models\Sequirityvlunerability;

class AuthController extends Controller
{
    
    public $successStatus = 200;
    public function __construct()
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->middleware('jwt.verify', ['except' => ['register','verify_otp','resend_otp','refresh_token']]);
    }
/**********************************
   Date        : 15/03/2024
   Description :  Refresh token
**********************************/
    public function refresh_token(Request $request)
    {
	$validator = Validator::make($request->all(), [
            'random_string'  => 'required'
	]);
    if ($validator->fails())
    {
        $errors  = json_decode($validator->errors());
        $random_string=isset($errors->random_string[0])? $errors->random_string[0] : '';
         if($random_string)
        {
          $msg = $random_string;
        }
        return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
    }
    $checkexist = DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->exists();
    if($checkexist==true)
    {
	    $token     = $request->bearerToken();
        $vlunerability_id=DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->first();
	    $new_token = auth()->tokenById($vlunerability_id->user_id);
	    $data      = $this->createNewToken($new_token);
    	$user=DB::table('users')->where('id', $request->user_id)->first();
	    return response()->json(['statusCode' => $this-> successStatus,'data'=>$user,'token'=> $data,'success' => 'success'], $this-> successStatus);
	}
	else
	{
		$error="User does not exist.";
		return response()->json(['message'=>$error,'statusCode'=>401,'data'=>[],'success' => 'error'],$this-> successStatus);
	}
    }        
/**********************************
   Date        : 14/03/2024
   Description :  verify otp
**********************************/    
    public function verify_otp(Request $request)
    {
		$validator = Validator::make($request->all(), [
            'phone_number'=> 'required|min:10|numeric',
	        'otp'  => 'required|numeric'
	    ]);
	    if ($validator->fails())
	    {
	        $errors  = json_decode($validator->errors());
	        $phone_number=isset($errors->phone_number[0])? $errors->phone_number[0] : '';
	        $otp=isset($errors->otp[0])? $errors->otp[0] : '';
	         if($phone_number)
	        {
	          $msg = $phone_number;
	        }
	        else if($otp)
	        {
	          $msg = $otp;
	        }
	        return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
	    }
        $checkexist   = DB::table('users')->where('phone_number',$request->phone_number)->exists();
        $otp          = DB::table('users')->where('otp', $request->otp)->exists();
        if($checkexist==true && $otp==true)
        {
			$user     = User::where('phone_number', '=', $request->phone_number)->first();
			$userToken=JWTAuth::fromUser($user);
			$token   = $this->createNewToken($userToken);
			$message="OTP verified successfully!";
            $string=Sequirityvlunerability::where('user_id',$user->id)->first();
			return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$user,'token'=>$token,'success' => 'success','random_string'=>$string->random_string], $this-> successStatus);
        }
        else
        {
			$error="OTP does not match.";
			return response()->json(['message'=>$error,'statusCode'=>401,'data'=>[],'success' => 'error'],$this-> successStatus);
        }
        
    }
/**********************************
   Date        : 14/03/2024
   Description :  resend otp
**********************************/
    public function resend_otp(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:10|numeric'
	    ]);
	    if ($validator->fails())
	    {
	        $errors  = json_decode($validator->errors());
	        $phone_number=isset($errors->phone_number[0])? $errors->phone_number[0] : '';
	        if($phone_number)
	        {
	          $msg = $phone_number;
	        }
	        return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
	    }
        if(DB::table('users')->where('phone_number', $request->phone_number)->exists()==true)
        {
        	$otp = random_int(1000, 9999);
        	DB::table('users')->where('phone_number', $request->phone_number)->update(['otp'=>$otp]);
        	$userdata=DB::table('users')->where('phone_number', $request->phone_number)->first();
			$message="OTP has been resend to the registerd phone number successfully!";
            $string=Sequirityvlunerability::where('user_id',$userdata->id)->first();
			return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$userdata,'otp'=>$otp,'success' => 'success','random_string'=>$string->random_string], $this-> successStatus);
        }
        else
        {
        	$error="Not found.";
			return response()->json(['message'=>$error,'statusCode'=>401,'data'=>[],'success' => 'error'],$this-> successStatus);
        }
    }
/****************************************
   Date        : 14/03/2024
   Description :  update user section
****************************************/    
    public function update_profile(Request $request)
    {
    	if(auth()->user())
        {
        	
            if($request->file('image') !== null)
            {
                $validator   = Validator::make($request->all(), [
                 'image'      =>'required|file|mimes:jpeg,jpg,png',
                ]);
                if ($validator->fails())
                {
                    $errors  = json_decode($validator->errors());
                    $image=isset($errors->image[0])? $errors->image[0] : '';
                    if($image)
                    {
                        $msg = $image;
                    }
                    return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
                }
                $path = $request->file('image')->store('info_temp_propic');
                $file = $request->file('image');
                // $fileName = auth()->user()->id.'/'.time().'/'.$file->getClientOriginalName();
                $fileName =$file->getClientOriginalName();
                $file->move(public_path('profile_pic'), $fileName);
            }
            else
            {
                $validator   = Validator::make($request->all(), [
                'full_name'  => 'required'
                ]);
                if ($validator->fails())
                {
                    $errors  = json_decode($validator->errors());
                    $full_name=isset($errors->full_name[0])? $errors->full_name[0] : '';
                    if($full_name)
                    {
                        $msg = $full_name;
                    }
                    return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
                }
                $fileName="";
            }
			
		    DB::table('users')->where('id', auth()->user()->id)->update(['name'=>$request->full_name,'about'=>$request->about,'image'=>$fileName]);	
		    $message="Profile added successfully!.";
			return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>[],'success' => 'success'], $this-> successStatus);
	    }
    }    
/******************************************
   Date        : 14/03/2024
   Description :  register a new user
******************************************/    
    public function register(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'phone_number'    => 'required|min:10|numeric'
        ]);
        if ($validator->fails())
        {
            $errors  = json_decode($validator->errors());
            $phone_number=isset($errors->phone_number[0])? $errors->phone_number[0] : '';
            if($phone_number)
            {
              $msg = $phone_number;
            }
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],200);
        }
        $check_exist=User::where('phone_number',$request->phone_number)->exists();
        $otp   = random_int(1000, 9999);
        if($check_exist==true)
        {
        	$user  =User::where('phone_number',$request->phone_number)->first();
        	$user->update(['otp'=> $otp]);
        	$register=User::where('phone_number',$request->phone_number)->first();
        	$exist="Already existing user";
            $sequirity_userid=Sequirityvlunerability::where('user_id',$register->id)->update([
                'user_id'=>$register->id,
                'random_string'=>substr(uniqid(), 0,25)
            ]);
        }
        else
        {
			$register                  = new User();
			$register->phone_number    = $request->phone_number;
			$register->otp             = $otp;
			$register->country_code    = '+91';
			$register->save();
			$exist="New user has been registerd";
            $sequirity_id=Sequirityvlunerability::create([
                'user_id'=>$register->id,
                'random_string'=>substr(uniqid(), 0,25)
            ]);
        }

        $smsres = $this->sentSMS("66b30bf1d6fc0577f035b213",$request->phone_number,$otp,"388423A1O8aeQmcyix66b6296dP1","",$register->name);
        $smsres = json_decode($smsres);

        if (isset($smsres->type) && $smsres->type == "success") 
        {
            $message="An otp has been successfully shared with your registered phone number!";
            return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$register,'otp'=>$otp,'success' => 'success','exist'=>$exist], $this-> successStatus);
        } else 
        {
            $message="Short Credits. Please add credits to sending message!";
            return response()->json(['message'=>$message, 'statusCode' => 401,'data'=>[],'success' => 'failour'], $this-> successStatus);
        }


		
    }

/****************************************
   Date        : 14/03/2024
   Description :  get user profile
****************************************/
function sentSMS($tempid, $mobile, $otp, $authkey, $realTimeResponse, $user_name) {
        try {

                $curl = curl_init();
                $req_param=json_encode([
                    "template_id" => $tempid,
                    "short_url" => 1,
                    "realTimeResponse" => 1,
                    "recipients" => [
                        [
                            "mobiles" => $mobile,
                            "var" => $otp
                        ]
                    ]
                ]);
                curl_setopt_array($curl, [
                CURLOPT_URL => "https://control.msg91.com/api/v5/flow",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false),
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $req_param,
                CURLOPT_HTTPHEADER => [
                    "accept: application/json",
                    "authkey: 388423A1O8aeQmcyix66b6296dP1",
                    "content-type: application/json"
                ],
                ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                echo "cURL Error #:" . $err;
                } else {
                return $response;
                }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            //return response()->json(['message' => $msg, 'success' => 'error', 'data' => [], 'statusCode' => 401], 401);
        }
    }
    public function userProfile()
    {
    	try
    	{
	    	if(auth()->user())
	        {
		        $user   = DB::table('users')->where('id', auth()->user()->id)->first();
				$message="Result fetched successfully!";
				return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$user,'profile_pic'=>asset('profile_pic/'.auth()->user()->image),'success' => 'success'], $this-> successStatus);
		    }
	    }
	    catch (\Exception $e) 
	    {
	        return response()->json([
	            'success'    => 'error',
	            'statusCode' => 500,
	            'data'       => [],
	            'message'    => $e->getMessage(),
	        ]);
        }
    }
    /****************************************
       Date        : 14/03/2024
       Description :  get user profile
    ****************************************/    
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }    
   
}