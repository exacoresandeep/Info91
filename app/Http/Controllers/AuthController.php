<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Group;
use Validator;
use DB;
use Carbon\Carbon;
use DateTime;
use JWTAuth;
use Illuminate\Support\Facades\Schema;
use App\Models\Sequirityvlunerability;
use App\Models\FirstCategory;
use App\Models\SecondCategory;
use App\Models\ThirdCategory;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Traits\PushNotification;
class AuthController extends Controller
{
    use PushNotification;
    public $successStatus = 200;

    public function __construct(){
        date_default_timezone_set('Asia/Kolkata');
        $this->middleware('jwt.verify', ['except' => ['register','verify_otp','resend_otp','refresh_token']]);
    }
    
    public function refresh_token(Request $request){
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
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],422);
        }
        $checkexist = DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->exists();
        if($checkexist==true)
        {
            $token     = $request->bearerToken();
            $vlunerability_id=DB::table('sequirityvlunerability')->where('random_string', $request->random_string)->first();
            $new_token = auth()->tokenById($vlunerability_id->user_id);
            $data      = $this->createNewToken($new_token);
            $user=DB::table('users')->where('id', $request->user_id)->first();
            $temp = $user->image;
            $user->image=env('APP_URL').'/public/profile_pic/'.$temp;
            return response()->json(['statusCode' => $this-> successStatus,'data'=>$user,'token'=> $data,'success' => 'success'], $this-> successStatus);
        }
        else
        {
            $error="User does not exist.";
            return response()->json(['message'=>$error,'statusCode'=>400,'data'=>[],'success' => 'error'],400);
        }
    }        
    public function updateFcmToken(Request $request)
    {
        // Validate the request
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        if (!$user) {
            return response()->json(['status' => 'error',
                'success'=>'error','statusCode'=>401,
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Update the FCM token
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success'=>'success','statusCode'=>200,
            'message' => 'FCM token updated successfully.',
        ]);
    }  
    
    public function verify_otp(Request $request){
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
	        return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],422);
	    }
        $checkexist   = DB::table('users')->where('phone_number',$request->phone_number)->where('otp', $request->otp)->exists();
        
        if($checkexist==true)
        {
            $user     = User::where('phone_number', '=', $request->phone_number)->first();
            DB::table('users')->where('phone_number', $request->phone_number)->update(['otp_verified'=>'1']);
			$userToken=JWTAuth::fromUser($user);
			$message="OTP verified successfully!";
            $string=Sequirityvlunerability::where('user_id',$user->id)->first();
            $user->user_id = $user->id; // Rename id to user_id
            unset($user->id);
            $temp = $user->image;
            $user->image=env('APP_URL').'/public/profile_pic/'.$temp;
			return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$user,'token'=>$userToken,'success' => 'success','random_string'=>$string->random_string], $this-> successStatus);
        }
        else
        {
			$error="OTP does not match.";
			return response()->json(['message'=>$error,'statusCode'=>300,'data'=>[],'success' => 'error'],300);
        }
        
    }
    
    public function resend_otp(Request $request){
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
	        return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],422);
	    }
        if(DB::table('users')->where('phone_number', $request->phone_number)->exists()==true)
        {
        	$otp = random_int(1000, 9999);
        	 if($request->phone_number=='2345678901'){
                $otp='1234';
            }
        	$update=DB::table('users')->where('phone_number', $request->phone_number)->update(['otp'=>$otp]);
        	$userdata=DB::table('users')->where('phone_number', $request->phone_number)->first();
            $userdata->user_id = $userdata->id; // Rename id to user_id
            unset($userdata->id);
            $smsres = $this->sentSMS("66b30bf1d6fc0577f035b213",$userdata->country_code.$userdata->phone_number,$otp,"388423A1O8aeQmcyix66b6296dP1","",$userdata->name);
            $smsres = json_decode($smsres);
            $user  =User::where('phone_number',$request->phone_number)->first();
            $token = JWTAuth::fromUser($user);


            if (isset($smsres->type) && $smsres->type == "success") 
            {
                $message="OTP has been resend to the registerd phone number successfully!";
                $string=Sequirityvlunerability::where('user_id',$userdata->user_id)->first();
                $temp = $userdata->image;
                $userdata->image=env('APP_URL').'/public/profile_pic/'.$temp;
			    return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$userdata,'success' => 'success','random_string'=>$string->random_string], $this-> successStatus);
            } else 
            {
                $message="Short Credits. Please add credits to sending message!";
                return response()->json(['message'=>$message, 'statusCode' => 300,'data'=>[],'success' => 'error'], 300);
            }
            
			
        }
        else
        {
        	$error="Not found.";
			return response()->json(['message'=>$error,'statusCode'=>400,'data'=>[],'success' => 'error'],400);
        }
    }

    public function update_profile(Request $request){
        try {
            if (auth()->user()) {
                $validator = Validator::make($request->all(), [
                    // 'full_name' => 'required',
                    'pincode'   => 'required',
                    // 'about'     => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors(), 'statusCode' => 422, 'data' => [], 'success' => 'error'], 422);
                }

                if ($request->file('image') !== null) {
                    $validator = Validator::make($request->all(), [
                        'image' => 'file|mimes:jpeg,jpg,png',
                    ]);

                    if ($validator->fails()) {
                        $imageErrors = $validator->errors();
                        $imageMessage = $imageErrors->first('image') ?? '';
                        return response()->json(['message' => $imageErrors, 'statusCode' => 422, 'data' => [], 'success' => 'error'], 422);
                    }

                    // Store the uploaded image in a folder and generate file name
                    $file = $request->file('image');
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = auth()->user()->id . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $fileExtension;
                    $file->move(public_path('profile_pic'), $fileName);

                    // Update the user's profile with the image path
                    DB::table('users')->where('id', auth()->user()->id)->update([
                        'name'    => $request->full_name ?? null,
                        'about'   => $request->about ?? null,
                        'pincode' => $request->pincode,
                        'image'   => $fileName,
                    ]);
                } else {
                    // Update without image
                    DB::table('users')->where('id', auth()->user()->id)->update([
                        'name'    => $request->full_name ?? null,
                        'about'   => $request->about ?? null,
                        'pincode' => $request->pincode,
                    ]);
                }

                $user  =User::where('id', auth()->user()->id)->first();
                $user->user_id = $user->id; // Rename id to group_id
                unset($user->id);
                $temp = $user->image;
                $user->image=env('APP_URL').'/public/profile_pic/'.$temp;
                $message="Profile updated successfully!.";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$user,'success' => 'success'], $this-> successStatus);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'success' => 'error', 'data' => [], 'statusCode' => 500], 500);
        }
    }  

    public function logout(Request $request)
    {
        $user = auth()->user();
    
        // Check if the user is authenticated
        if (!$user) {
            return response()->json([
                'success' => 'error',
                'statusCode' => 401,
                'message' => 'User not authenticated.'
            ], 401);
        }
        
        $user->fcm_token = null;
        $user->save();
        JWTAuth::invalidate(JWTAuth::getToken());
        // JWTAuth::invalidate(JWTAuth::getRefreshToken());
        return response()->json([
            'success' => 'success',
            'statusCode' => 200,
            'message' => 'Logged out successfully and FCM token cleared.'
        ]);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number'    => 'required|min:10|numeric',
            'device_id' =>'required'
        ]);
        if ($validator->fails())
        {
            $errors  = json_decode($validator->errors());
            return response()->json(['message' =>$validator->errors(),'statusCode'=>422,'data'=>[],'success'=>'error'],422);
        }
        $smsres=[];
        $blocked=User::where('phone_number',$request->phone_number)->where('status',"2")->exists();
        if($blocked===true){    
            $message="You have been blocked. Please contact administrator!";
            return response()->json(['message'=>$message, 'statusCode' => 400,'data'=>[],'success' => 'error'], 400);
        }
        $check_exist=User::where('phone_number',$request->phone_number)->exists();
        $device_exist=User::where('phone_number',$request->phone_number)
            ->where('device_id',$request->device_id)->exists();
         
        $otp   = random_int(1000, 9999);
        if($request->phone_number=='2345678901'){
            $otp='1234';
        }
        $user  =User::where('phone_number',$request->phone_number)->first();
        if($device_exist===false){
            $userupdate=User::where('phone_number',$request->phone_number)->update(['otp'=> $otp,'otp_verified'=>'0','device_id'=>$request->device_id]);
        }
        $otp_verified=User::where('phone_number',$request->phone_number)->where('otp_verified','1')->exists();
       
        if($check_exist===true){    
            
            $register=User::where('phone_number',$request->phone_number)->first();
            $token = JWTAuth::fromUser($user);
            $message="Verified Customer";
            $register->user_id = $register->id; // Rename id to user_id
            unset($register->id);
            if($otp_verified===false){ 
                $userupdate=User::where('phone_number',$request->phone_number)->update(['otp'=> $otp,'otp_verified'=>'0']);
                $smsres = $this->sentSMS("66b30bf1d6fc0577f035b213",$register->country_code.$request->phone_number,$otp,"388423A1O8aeQmcyix66b6296dP1","",$register->name);
                $smsres = json_decode($smsres);
                $user  =User::where('phone_number',$request->phone_number)->first();
                if (isset($smsres->type) && $smsres->type == "success") 
                {
                    $register->user_id = $register->id;
                    unset($register->id);
                    $message="An otp has been successfully shared with your registered phone number!";
                    return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$register,'otp'=>$otp,'success' => 'success','exist'=>$otp_verified,'token' => $token], $this-> successStatus);
                } else 
                {
                    $message="Short Credits. Please add credits to sending message!";
                    return response()->json(['message'=>$message, 'statusCode' => 200,'data'=>[],'success' => 'error'], $this-> successStatus);
                }
            }
            return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$register,'success' => 'success','exist'=>true,'token' => $token], $this-> successStatus);
        }else{
			$register                  = new User();
			$register->phone_number    = $request->phone_number;
			$register->device_id    = $request->device_id;
			$register->otp             = $otp;
			$register->country_code    = '+91';
			$register->save();
            $sequirity_id=Sequirityvlunerability::create([
                'user_id'=>$register->id,
                'random_string'=>substr(uniqid(), 0,25)
            ]);
            $smsres = $this->sentSMS("66b30bf1d6fc0577f035b213",$register->country_code.$request->phone_number,$otp,"388423A1O8aeQmcyix66b6296dP1","",$register->name);
            $smsres = json_decode($smsres);
            $user  =User::where('phone_number',$request->phone_number)->first();
            $token = JWTAuth::fromUser($user);
            if (isset($smsres->type) && $smsres->type == "success"){
                $register->user_id = $register->id;
                unset($register->id);
                $message="An otp has been successfully shared with your registered phone number!";
                return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$register,'otp'=>$otp,'success' => 'success','exist'=>$otp_verified,'token' => $token], $this-> successStatus);
            } else {
                $message="Short Credits. Please add credits to sending message!";
                return response()->json(['message'=>$message, 'statusCode' => 400,'data'=>[],'success' => 'error'], 400);
            }
        }		
    }
    
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
                    "authkey: 388423A1O8aeQmcyix66b6296dP1",//388423A1O8aeQmcyix66b6296dP1
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

    public function userProfile(){
        

    	try
    	{
	    	if(auth()->user())
	        {
	            $token = request()->bearerToken() ?? null;
		        $user   = DB::table('users')->where('id', auth()->user()->id)->first();
				$message="Result fetched successfully!";
                $temp = $user->image;
                $user->image=env('APP_URL').'/public/profile_pic/'.$temp;
                $user->user_id = $user->id; // Rename id to group_id
                unset($user->id);
				return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$user,'success' => 'success','current_token'=>$token,'user_data'       => [
                        'user'  => $user,
                        'token' => $token,
                    ],], $this-> successStatus);
		    }
		  else{
		         return response()->json([
                    'success' => 'error',
                    'statusCode' => 400,
                    'message' => 'User not found.','current_token'=>""
                ], 400);
		    }
	    }
	    catch (\Exception $e) 
	    {
	        return response()->json([
	            'success'    => 'error',
	            'statusCode' => 500,
	            'data'       => [],
	            'message'    => $e->getMessage(),'current_token'=>""
	        ]);
        }
    }
     
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }    
   
    public function generateId (){
        $micro = gettimeofday()['usec'];
        $todate =  date("YmdHis");
        $alpha = substr(md5(rand()), 0, 2);
        return($todate.$micro.$alpha);
    }

    public function fileUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,mp4,mp3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Get the file extension
            $extension = $file->getClientOriginalExtension();
            
            // Generate a new file name based on timestamp and user ID
            $userId = auth()->id(); // Assuming user is authenticated
            $filename = time() . '_' . $userId . '.' . $extension;
            
            // Store the file with the new name in the 'uploads' directory
            // $path = $file->storeAs('uploads', $filename);
            $file->move(public_path('upload_files'), $filename);
            $path=env('APP_URL').'/upload_files/'.$filename;
            
            return response()->json([
                'message' => 'File uploaded successfully.',
                'statusCode' => 200,
                'data' => [
                    'filePath' => $path,
                    'fileName' => $filename, // Return the new file name
                ],
                'success' => 'success',
            ], 200);
        }

        return response()->json([
            'message' => 'No file uploaded.',
            'statusCode' => 400,
            'data' => [],
            'success' => 'error',
        ], 400);
    }


    public function groupCreation(Request $request){
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|max:256',
            'type' => 'required|in:business,non-business,community',
            'address' => 'required|string|max:500',
            'category1' => 'nullable|string|max:256',
            'category2' => 'nullable|string|max:256',
            'category3' => 'nullable|string|max:256',
            'plan_id' => 'required|integer',
            'email' => 'nullable|string|max:256',
            'tag_key_1' => 'nullable|string|max:256',
            'tag_key_2' => 'nullable|string|max:256',
            'tag_key_3' => 'nullable|string|max:256',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
    
        // Retrieve validated data
        $validatedData = $validator->validated();
        
        // Set the 'created_by' field
        $validatedData['created_by'] = auth()->user()->id;
        $validatedData['mobile_number'] = auth()->user()->phone_number;
    
        try {
            // Generate a unique ID (you can modify this logic as needed)
            $validatedData['id'] = $this->generateId(); // Assumes generateId() is defined elsewhere
    
            if ($validatedData['type'] == 'community') {
                $validatedData['status'] = '1';
                $message = "Group creation request sent successfully.";
            } else {
                $validatedData['status'] = '0';
                $message = "Group creation request sent successfully and our admin will contact you soon.";
            }
    
            // Create a new group
            $group = Group::create($validatedData);
    
            // Return a success response
            return response()->json(['success'=>'success', 'statusCode' => 200,'message' => $message], 200);
        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while creating the group.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }
    }
     public function groupList(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|string|max:22',
            ]);
    
            $userId = $validatedData['user_id'];
            $groups = \App\Models\Group::where('status', "!=", '2')->get();
    
            $userGroups = [];
    
            foreach ($groups as $group) {
                $groupuserTable = 'group_users_' . $group->id;
                $groupmessageTable = 'group_message_' . $group->id;
    
                // Check if the group users table exists
                if (Schema::hasTable($groupuserTable)) {
                    $isUserInGroup = DB::table($groupuserTable)
                        ->where('user_id', $userId)
                        ->exists();
    
                    if ($isUserInGroup) {
                        $groupData = $group->toArray();
                        $groupData['group_id'] = $groupData['id'];
                        unset($groupData['id']);
                        $groupData['profile_image'] = env('APP_URL') . '/public/group_profile_pic/' . $groupData['profile_image'];
                        $groupData['cover_image'] = env('APP_URL') . '/public/group_cover_pic/' . $groupData['cover_image'];
                        $groupData['unread_count'] = null;
                        $groupData['group_approved_flag'] = $this->getGroupApprovalStatus($group->status);
                        
                        $isAdmin = DB::table($groupuserTable)
                        ->where('user_id', $userId)
                        ->where('role', '1')
                        ->exists();
                        
                        if ($isAdmin) {
                            $groupData['owngroup_flag'] = true;
                            $msg = DB::table($groupmessageTable)
                                ->join('users', "$groupmessageTable.user_id", '=', 'users.id')
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number'
                                )
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        } else {
                           $groupData['owngroup_flag'] = false;
                            $msg = DB::table($groupmessageTable)
                                ->join('users', DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT(users.id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->join($groupuserTable, DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT($groupuserTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number',
                                    "$groupuserTable.role"
                                )
                                ->where(function ($query) use ($groupuserTable, $userId) {
                                    $query->whereRaw("$groupuserTable.role = 1")
                                        ->orWhereRaw("$groupuserTable.user_id = $userId");
                                })
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        }
    
                        $groupData['last_message'] = $msg ? [
                            'message' => $msg->message,
                            'type' => $msg->type,
                            'user_id' => $msg->user_id,
                            'name' => $msg->name,
                            'phone_number' => $msg->phone_number,
                            'last_message_time' => $msg->created_at,
                            'is_me' => $msg->user_id ===$userId,
                        ] : null;
    
                        $userGroups[] = $groupData;
                    }
                } else {
                    // Handle case where table doesn't exist
                    if ($group->created_by === $userId) {
                        $group->owngroup_flag = true;
    
                        $groupData = $group->toArray();
                        $groupData['group_id'] = $groupData['id'];
                        unset($groupData['id']);
                        $groupData['profile_image'] = env('APP_URL') . '/public/group_profile_pic/' . $groupData['profile_image'];
                        $groupData['cover_image'] = env('APP_URL') . '/public/group_cover_pic/' . $groupData['cover_image'];
                        $groupData['unread_count'] = null;
                        $groupData['last_message'] = null;
                        $groupData['owngroup_flag'] = true;
                        $groupData['group_approved_flag'] = $this->getGroupApprovalStatus($group->status);
    
                        $userGroups[] = $groupData;
                    }
                }
            }
    
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $userGroups,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while listing the group.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   

    private function getGroupApprovalStatus($status) {
        switch ($status) {
            case 0:
                return "not_approved";
            case 1:
                return "approved";
            case 2:
                return "rejected";
            default:
                return "unknown"; // In case of an unexpected status value
        }
    }
            
    public function groupSearch(Request $request) {
        try {
            // Validate the incoming request to ensure `user_id` and `search_key` are provided
            $validatedData = $request->validate([
                'user_id' => 'required|string|max:22',
                'search_key' => 'required|string|max:255',
            ]);
    
            $userId = $validatedData['user_id'];
            $searchKey = $validatedData['search_key'];
    
            // Fetch all active groups whose name contains the search keyword (case-insensitive)
            $groups = \App\Models\Group::where('status', '1')
                ->where('group_name', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_1', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_2', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_3', 'LIKE', '%' . $searchKey . '%')
                ->get();
    
            $userGroups = [];
    
            // Iterate through each group to check if the user is in the group_users_<group_id> table
            foreach ($groups as $group) {
                $groupuserTable = 'group_users_' . $group->id;
                $groupmessageTable = 'group_message_' . $group->id;
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupuserTable)) {
                    $isUserInGroup = DB::table($groupuserTable)
                        ->where('user_id', $userId)
                        ->exists();
    
                    if ($isUserInGroup) {
                        // Add owngroup_flag and additional fields directly to the group object
                        // $group->owngroup_flag = $group->created_by === $userId;
                        $group->group_id = $group->id; // Rename id to group_id
                        unset($group->id);
                        $tempPro = $group->profile_image;
                        $group->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                        $tempCov = $group->cover_image;
                        $group->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                        // Additional fields
                        $group->unread_count = null;
                        // $group->last_message = 'Coming soon';
                        $isAdmin = DB::table($groupuserTable)
                            ->where('user_id', $userId)
                            ->where('role', '1')
                            ->exists();
    
                        if ($isAdmin) {
                            $group->owngroup_flag = true;
                            $msg = DB::table($groupmessageTable)
                                ->join('users', "$groupmessageTable.user_id", '=', 'users.id')
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number'
                                )
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        } else {
                            $group->owngroup_flag = false;
                            $msg = DB::table($groupmessageTable)
                                ->join('users', DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT(users.id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->join($groupuserTable, DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT($groupuserTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number',
                                    "$groupuserTable.role"
                                )
                                ->where(function ($query) use ($groupuserTable, $userId) {
                                    $query->whereRaw("$groupuserTable.role = 1")
                                        ->orWhereRaw("$groupuserTable.user_id = $userId");
                                })
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        }
    
                        $group['last_message'] = $msg ? [
                            'message' => $msg->message,
                            'type' => $msg->type,
                            'user_id' => $msg->user_id,
                            'name' => $msg->name,
                            'phone_number' => $msg->phone_number,
                            'last_message_time' => $msg->created_at,
                            'is_me' => $msg->user_id ===$userId,
                        ] : null;
                        // Add the group to the user's group list
                        $userGroups[] = $group;
                    }
                }
            }
    
            // Return a success response with the filtered groups
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $userGroups
            ], 200);
    
        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function publicGroupSearch(Request $request) {
        try {
            // Validate the incoming request to ensure `user_id` and `search_key` are provided
            $validatedData = $request->validate([
                'user_id' => 'required|string|max:22',
                'search_key' => 'nullable|string|max:255',
            ]);
            
            $userId = $validatedData['user_id'];
            $searchKey = $validatedData['search_key'] ?? null;
            if (is_null($searchKey) || trim($searchKey) === '') {
                return response()->json([
                    'status' => 'success',
                    'statusCode' => 200,
                    'message' => 'No groups found.',
                    'data' => []
                ], 200);
            }
            // Fetch all active groups whose name contains the search keyword (case-insensitive)
            $groups = \App\Models\Group::where('status', '1')
                ->where('group_name', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_1', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_2', 'LIKE', '%' . $searchKey . '%')
                ->orWhere('tag_key_3', 'LIKE', '%' . $searchKey . '%')
                ->get();
    
            $userGroups = [];
    
            // Iterate through each group to check if the user is in the group_users_<group_id> table
            foreach ($groups as $group) {
                $groupuserTable = 'group_users_' . $group->id;
                $groupmessageTable = 'group_message_' . $group->id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupuserTable)) {
                    $isUserInGroup = DB::table($groupuserTable)
                        ->where('user_id', $userId)
                        ->exists();
    
                    // Add flags and additional fields to each group object
                    $group->joined_flag = $isUserInGroup;
                    $group->group_id = $group->id; // Rename id to group_id
                    unset($group->id);
                    $tempPro = $group->profile_image;
                        $group->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                        $tempCov = $group->cover_image;
                        $group->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                    // Additional fields
                    $group->unread_count = null;
                    $isAdmin = DB::table($groupuserTable)
                            ->where('user_id', $userId)
                            ->where('role', '1')
                            ->exists();
    
                        if ($isAdmin) {
                            $msg = DB::table($groupmessageTable)
                                ->join('users', "$groupmessageTable.user_id", '=', 'users.id')
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number'
                                )
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        } else {
                            $msg = DB::table($groupmessageTable)
                                ->join('users', DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT(users.id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->join($groupuserTable, DB::raw("CONVERT($groupmessageTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"), '=', DB::raw("CONVERT($groupuserTable.user_id USING utf8mb4) COLLATE utf8mb4_unicode_ci"))
                                ->select(
                                    "$groupmessageTable.*",
                                    'users.name',
                                    'users.phone_number',
                                    "$groupuserTable.role"
                                )
                                ->where(function ($query) use ($groupuserTable, $userId) {
                                    $query->whereRaw("$groupuserTable.role = 1")
                                        ->orWhereRaw("$groupuserTable.user_id = $userId");
                                })
                                ->orderBy("$groupmessageTable.created_at", 'desc')
                                ->first();
                        }
    
                        $group['last_message'] = $msg ? [
                            'message' => $msg->message,
                            'type' => $msg->type,
                            'user_id' => $msg->user_id,
                            'name' => $msg->name,
                            'phone_number' => $msg->phone_number,
                            'last_message_time' => $msg->created_at,
                            'is_me' => $msg->user_id ===$userId,
                        ] : null;
    
                    // Add the group to the user's group list
                    $userGroups[] = $group;
                }
            }
    
            // Return a success response with the filtered groups
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $userGroups
            ], 200);
    
        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function groupType(){
        try {

            $user = auth()->user();
            if($user){
                $type_arr=$type_arr = [
                    ['name' => 'Bussiness Group', 'value' => 'bussiness'],
                    ['name' => 'Non Bussiness Group', 'value' => 'non-business']
                ];
            }
            else{
                $type_arr=[];
            }

            // Return a success response with the filtered groups
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $type_arr
            ], 200);

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function firstCategories(){
        try {

            $user = auth()->user();
            if($user){
                $data = FirstCategory::select('id as first_category_id','first_category_name')->get();
            }
            else{
                $data=[];
            }

            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function secondCategories($id) {
        try {
            $user = auth()->user();
            
            if ($user) {
                // Retrieve second categories where first_category_id matches the given ID
                $data = SecondCategory::with(['firstCategory' => function($query) {
                        $query->select('id', 'first_category_name'); // Specify fields to retrieve from firstCategory
                    }])
                    ->select('id as second_category_id', 'second_category_name', 'first_category_id') // Select fields from SecondCategory
                    ->where('first_category_id', $id)
                    ->get()
                    ->map(function ($item) {
                        unset($item->firstCategory->id);
                        return $item;
                    });
            } else {
                $data = [];
            }
    
            // Return a success response with the filtered second categories
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $data
            ], 200);
    
        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching the second categories.',
                'error' => $e->getMessage(), // For debugging; omit in production
            ], 500);
        }
    }
    

    public function thirdCategories($id) {
        try {
            $user = auth()->user();
            
            if ($user) {
                // Retrieve third categories where second_category_id matches the given ID
                $data = ThirdCategory::with(['SecondCategory' => function($query) {
                        $query->select('id', 'second_category_name'); // Specify fields to retrieve from SecondCategory
                    }])
                    ->select('id as third_category_id', 'third_category_name', 'second_category_id') // Select fields from ThirdCategory
                    ->where('second_category_id', $id) // Filter by second_category_id
                    ->get()
                    ->map(function ($item) {
                        unset($item->SecondCategory->id);
                        return $item;
                    });
            } else {
                $data = [];
            }
    
            // Return a success response with the filtered third categories
            $message = "List fetched Successfully";
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => $data
            ], 200);
    
        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching the third categories.',
                'error' => $e->getMessage(), // For debugging
            ], 500);
        }
    }

    public function businessGroupPlanList(){
        try {
            // Fetch all plans from the database
            $plans = Plan::all();

            // Return a success response with the plan data
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Plans fetched successfully',
                'data' => $plans
            ], 200);
            
        } catch (\Exception $e) {
            // Handle any errors and return a failure response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching the plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function validatePincode(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'pincode' => 'required|string|max:6|min:6',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                $pincode = $request->pincode;
                $check=DB::table('pincode')->where('pincode',$request->pincode)->exists();
                if($check===true){
                    $data=DB::table('pincode')->where('pincode',$request->pincode)->select('id as pincode_id','pincode','postname')->get();
                    $message = "Valid Pincode";
                    return response()->json([
                        'status' => 'success',
                        'statusCode' => 200,
                        'message' => $message,
                        'data' => $data
                    ], 200);

                }else{
                    $message = "Invalid Pincode";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 300,
                        'message' => $message,
                        'data' => []
                    ], 300);

                }
               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function joinPublicGroup(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                $user=DB::table('users')->where('id', auth()->user()->id)->first();
                
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id', $user->id)
                         ->where('status', '1')
                        ->exists();
                    if($isUserInGroup===true){
                        $message = "Already in group";
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => $message,
                            'data' => []
                        ], 400);
                    }
                    else{
                        $data = [
                            'user_id' => $user->id,    
                            'alarm_status' => '1',       
                            'status' => '1',       
                        ];
                    
                        DB::table($groupTable)->insert($data);
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => 'User joined group successfully',
                        ], 200);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function keyCheckExist(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'tag_key_1' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            if(auth()->user())
            {
                $groups = DB::table('groups')
                    ->where('tag_key_1', $request->tag_key_1)
                    ->exists();
                if($groups===true){
                    $message = "Key already exists";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }
                else{
                    return response()->json([
                        'status' => 'success',
                        'statusCode' => 200,
                        'message' => 'Available key',
                    ], 200);
                }
                
            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                    'error' => $e->getMessage(), 
                ], 401); 
            }
            

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), 
            ], 500); 
        }

    }

    public function leaveGroup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            // Check authentication
            if (auth()->user()) {
                $user = DB::table('users')->where('id', auth()->user()->id)->first();
    
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the group's user table exists
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id', $user->id)
                        ->exists();
    
                    if ($isUserInGroup) {
                        // Remove the user from the group
                        DB::table($groupTable)->where('user_id', $user->id)->update([
                            'status' => '0','role'=>'0']);
                            
                        $noAdmin = DB::table($groupTable)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->exists();
                        $memebrexist = DB::table($groupTable)
                            ->where('role', '0')
                            ->where('status', '1')
                            ->exists();
                        if($noAdmin===false && $memebrexist===true){ 
                            $firstuser = DB::table($groupTable)->where('status','1')->first();
                            DB::table($groupTable)->where('id', $firstuser->id)->update(['role'=>'1']);
                        }
                        
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => 'You left the group successfully',
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You is not member of this group',
                            'data' => [],
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => 'Invalid group',
                        'data' => [],
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while trying to leave the group.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500);
        }
    }

    public function groupInfoUpdate(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                // $user=DB::table('users')->where('id', auth()->user()->id)->first();
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                    if($isUserInGroup===true){

                        DB::table('groups')->where('id', $request->group_id)->update([
                            'mobile_number' => $request->mobile_number ?? null,
                            'alternative_number' => $request->alternative_number ?? null,
                            'whatsapp_number' => $request->whatsapp_number ?? null,
                            'contact_time' => $request->contact_time ?? null,
                            'holidays' => $request->holidays ?? null,
                            'purpose' => $request->purpose ?? null,
                            'address' => $request->address ?? null,
                            'website_link' => $request->website_link ?? null,
                            'youtube_link' => $request->youtube_link ?? null,
                            'googlemap_link' => $request->googlemap_link ?? null,
                            'email' => $request->email ?? null,
                            'tag_key_1' => $request->tag_key_1 ?? null,
                            'tag_key_2' => $request->tag_key_2 ?? null,
                            'tag_key_3' => $request->tag_key_3 ?? null,
                        ]);
                        

                        $message = "Group Info Updated";
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => $message,
                            'data' => []
                        ], 200);
                    }
                    else{
                        
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not an Admin',
                        ], 400);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }
    public function groupNameUpdate(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                // 'group_name' => 'required|string',
                'address' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                // $user=DB::table('users')->where('id', auth()->user()->id)->first();
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                    if($isUserInGroup===true){

                        DB::table('groups')->where('id', $request->group_id)->update([
                            // 'group_name' => $request->group_name ?? null,
                            'address' => $request->address ?? null
                        ]);
                        

                        $message = "Group Info Updated";
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => $message,
                            'data' => []
                        ], 200);
                    }
                    else{
                        
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not an Admin',
                        ], 400);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function groupInfo(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                // $user=DB::table('users')->where('id', auth()->user()->id)->first();
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        // ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                    if($isUserInGroup===true){
                        $adminrole = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('status', '1')
                        ->first();
                        $is_admin = ($adminrole && $adminrole->role == '1') ? true : false;
                        $group_info=DB::table('groups')->where('id', $request->group_id)->first();
                        
                        $tempPro = $group_info->profile_image;
                        $group_info->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                        $tempCov = $group_info->cover_image;
                        $group_info->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                        
                        $group_info->group_id = $group_info->id; // Rename id to group_id
                        unset($group_info->id);
                        $group_info->is_admin=$is_admin;
                        $message = "Group Info fetched Successfully";
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => $message,
                            'data' => $group_info
                        ], 200);
                    }
                    else{
                        
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not a Member',
                        ], 400);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function changeGroupUserStatus(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'user_id' => 'required',
                'role' => 'required|string',   //0 member //1 admin
                'status' => 'required|string', //1 active //0 inactive or delete
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                // $user=DB::table('users')->where('id', auth()->user()->id)->first();
                $groupTable = 'group_users_' . $request->group_id;
    
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isAdminInGroup = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                    if($isAdminInGroup===true){

                        $isUserInGroup = DB::table($groupTable)
                            ->where('user_id',auth()->user()->id)
                            // ->where('role', '1')
                            ->where('status', '1')
                            ->exists();
                        if($isUserInGroup===true){
                            $userdata = DB::table($groupTable)
                            ->where('user_id',auth()->user()->id)->where('status', '1')
                            ->first();
                            $group_update=DB::table($groupTable)->where('user_id', $request->user_id)->update([
                                'role'=>$request->role,
                                'status'=>$request->status
                            ]);
                            if($request->role=='0'){
                                $message = "Now user is a member";
                            }else{
                                $message = "Now user is an Admin";
                            }
                            if($request->status=='0'){
                                $message = "Removed user from the group by admin";
                            }
                            return response()->json([
                                'status' => 'success',
                                'statusCode' => 200,
                                'message' => $message
                            ], 200);
                        }
                        else{
                            
                            return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'Invalid User',
                            ], 400);
                        }
                    }
                    else{
                        
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not an Admin',
                        ], 400);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function groupProfile(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            if(auth()->user())
            {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;
               // $groupdata = DB::table('groups')
                //->where('id',$request->group_id)->where('status', '1')
                //->first();
                $groupdata = DB::table('groups')
                    ->join('users', 'groups.created_by', '=', 'users.id') 
                    ->where('groups.id', $request->group_id)
                    ->where('groups.status', '1')
                    ->select('groups.*', 'users.name as created_by_name')
                    ->first();
                $members=[];
                $is_admin=false;
                if(!empty($groupdata)){
                    $is_member=DB::table($groupTable)->where('user_id',auth()->user()->id)->exists();
                    //  dd(auth()->user()->id);
                    if($is_member===true){
                        $is_admin=DB::table($groupTable)->where('user_id', auth()->user()->id)->where('role','1')->exists();
                        if ($is_admin === true) {
                            $is_admin=true;
                            $members = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id') // Joining users table
                            ->select(
                                $groupTable . '.user_id',
                                $groupTable . '.role',
                                $groupTable . '.status',
                                'users.phone_number', 
                                'users.name', 
                                'users.image',
                                'users.about', 
                                'users.id' 
                            )
                            ->where($groupTable . '.status', '=', '1') 
                            ->orderBy($groupTable . '.role', 'desc')
                            ->get()->map(function ($member) {
                            // Add full URL for member image
                            $member->image = env('APP_URL') . '/public/profile_pic/' . $member->image;
                            return $member;
                        });
                        } else {
                            $members = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id') // Joining users table
                            ->select(
                                $groupTable . '.user_id',
                                $groupTable . '.role',
                                $groupTable . '.status',
                                'users.phone_number', // Selecting user's phone number
                                'users.image',
                                'users.name', // Selecting user's phone number
                                'users.id' // Selecting user's id, aliasing to avoid conflict
                            )
                            ->where($groupTable . '.role', '=', '1') // Filtering for role = 1
                            ->where($groupTable . '.status', '=', '1') // Filtering for status = 1
                            ->get()->map(function ($member) {
                            // Add full URL for member image
                            $member->image = env('APP_URL') . '/public/profile_pic/' . $member->image;
                            return $member;
                        });
                        }
                                           
                        
                    }
            
                    $data=$groupdata;
                    $total_member_count = DB::table($groupTable)->where('status','1')->count();
                    $data->member_flag=$is_member;
                    $data->is_admin=$is_admin;
                    $data->members_count = $total_member_count;
                    
                    $data->members=$members;
                    $data->created_by=$data->created_by_name;
                    
                    $tempPro = $data->profile_image;
                    $data->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                    $tempCov = $data->cover_image;
                    $data->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                    $banners = $banners = DB::table($bannerTable)->get()->map(function($banner) {
                        $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                        return $banner;
                    });
                    $data->banners=$banners;
                    $data->media_list=$this->listGroupMedia($request->group_id);
                    $message = "Group info fetched successfully";
                    return response()->json([
                        'status' => 'success',
                        'statusCode' => 200,
                        'message' => $message,
                        'data' => $data
                    ], 200); 
                }else{
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => 'Invalid Group',
                       // 'error' => $e->getMessage(), // For debugging; can be omitted in production
                    ], 400); // 500 Internal Server Error

                }         

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
               // 'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }

    }

    public function addGroupMessage(Request $request){
        try {
            
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'type' => 'required|in:audio,video,text,contact,image,document',
                
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
        
            // Retrieve validated data
            if(auth()->user())
            {
                // $user=DB::table('users')->where('id', auth()->user()->id)->first();
                $groupTable = 'group_users_' . $request->group_id;
                $groupmessageTable = 'group_message_' . $request->group_id;
                $group_details=DB::table('groups')->where('id',$request->group_id)->first();
                // Check if the user exists in the group's user table dynamically
                if (Schema::hasTable($groupTable)) {
                    $isMember = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('status', '1')
                        ->exists();
                    
                    if($isMember===true){
                        
                        $messageContent = $request->message;
                        if ($request->type === 'contact' && is_array($messageContent)) {
                            $messageContent = json_encode($messageContent); // Convert JSON data to string
                        }
                        
                        
                        DB::table($groupmessageTable)->insert([
                            'id'=> $this->generateId(),
                            'user_id' => auth()->user()->id,
                            'type' => $request->type,
                            'message' => $messageContent ?? null,
                            'reply_flag' => ($request->reply_message_id != null)?'1':'0',
                            'reply_message_id' => $request->reply_message_id ?? null,
                            'message_status' => 'Send',
                            'status' => '1',
                            'created_at' => now(),  // Optional: if you have a timestamp column
                            'updated_at' => now(),  // Optional: if you have a timestamp column
                        ]);
                        $isAdmin = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                        if($isAdmin===true){
                            $userList = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id')  
                            ->where($groupTable.'.status', '1')
                            ->select($groupTable.'.user_id','users.fcm_token')
                            ->get();
                            
                            $NotificationuserList = DB::table($groupTable)
                            ->leftjoin('users', $groupTable . '.user_id', '=', 'users.id')  
                            ->where($groupTable.'.status', '1')
                             ->where($groupTable.'.user_id','!=',auth()->user()->id)
                            ->select($groupTable.'.user_id','users.fcm_token')
                            ->get();
                            
                        }else{
                            $userList = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id')  
                            ->where($groupTable.'.status', '1')
                           
                            ->select($groupTable.'.user_id','users.fcm_token')
                            ->where($groupTable.'.role', '1')
                            ->get();
                            $NotificationuserList = DB::table($groupTable)
                            ->leftjoin('users', $groupTable . '.user_id', '=', 'users.id')  
                            ->where($groupTable.'.status', '1')
                            ->where($groupTable.'.user_id','!=',auth()->user()->id)
                            ->select($groupTable.'.user_id','users.fcm_token')
                            ->where($groupTable.'.role', '1')
                            ->get();
                        }
                       // dd($NotificationuserList);
                        $fcmTokens = $NotificationuserList->pluck('fcm_token')->toArray();

                       
                       // $this->sendMultiplePushNotifications($fcmTokens,$request->group_id,$group_details->group_name,$messageContent,$request->type,now());


                        $authUser = (object) [
                            'user_id' => auth()->id(),
                        ];
                        $userList->push($authUser);
                        $userList = $userList->unique('user_id');
                        $userListIds = $userList->pluck('user_id')->toArray();
                        $data = DB::table($groupmessageTable)
                            ->join("users", $groupmessageTable . '.user_id', '=', 'users.id')
                            ->leftJoin($groupmessageTable . ' as replies', $groupmessageTable . '.reply_message_id', '=', 'replies.id')
                            ->leftJoin('users as reply_users', 'replies.user_id', '=', 'reply_users.id')
                            ->select(
                                $groupmessageTable . '.id as message_id',
                                $groupmessageTable . '.user_id',
                                $groupmessageTable . '.type as type',
                                $groupmessageTable . '.message',
                                $groupmessageTable . '.message_status',
                                $groupmessageTable . '.created_at',
                                $groupmessageTable . '.reply_flag',
                                $groupmessageTable . '.reaction_flag',
                                'users.image',
                                'users.phone_number',
                                'users.name',
                                'replies.id as reply_message_id',
                                'replies.message as reply_message',
                                'replies.type as reply_type',
                                'replies.user_id as reply_user_id',
                                'reply_users.name as reply_user_name',
                                'reply_users.phone_number as reply_user_phone_number'
                            )
                            ->whereIn($groupmessageTable . '.user_id', $userListIds)
                            ->orderBy('created_at', 'desc')
                            // ->limit(100)
                            ->get();
                    
                            // Map to format each item with full image URL and a structured `reply_details` array
                            $data = $data->map(function ($item) {
                            // Set the main image URL
                            $item->image = env('APP_URL') . '/public/profile_pic/' . ($item->image ?? 'default.jpg');
                            $messageContent = $item->message;
                            $item->contact_list=[];
                            if ($item->type === 'contact') {
                                $item->message=null;
                                $item->contact_list = json_decode($messageContent); // Convert JSON data to string
                            } 
                            $item->reply_flag = (bool)$item->reply_flag; // Ensure this is set correctly
                            $item->reaction_flag = (bool)$item->reaction_flag; 
                            $createdAt = Carbon::parse($item->created_at);
                            $item->date = $createdAt->format('Y-m-d'); // Format date (e.g., 2024-11-06)
                            $item->time = $createdAt->format('h:i A');
                            $item->is_me = ($item->user_id==auth()->id()) ? true :false;
                            $basePath = env('APP_URL') . '/public/upload_files/';
                            $item->filesize=null;
                            $item->filename=null;
                            $item->filetype=null;
                            $item->filepages=0;
                            if (in_array($item->type, ['image', 'document', 'audio', 'video'])) {
                                $item->filename=$item->message;
                                $item->filetype = pathinfo($item->filename, PATHINFO_EXTENSION);
                                $filePath = public_path('upload_files/' . $item->filename);
                                if (file_exists($filePath)) {
                                    $item->filesize = filesize($filePath); 
                                    $item->filesize = round(filesize($filePath) / 1024 / 1024, 2) . ' MB';
                                }
                                if (in_array(strtolower($item->filetype), ['pdf'])) {
                                    
                                
                                    if (file_exists($filePath)) {
                                        try {
                                            $imagick = new \Imagick();
                                            $imagick->pingImage($filePath);
                                            $item->filepages = $imagick->getNumberImages();
                                        } catch (\Exception $e) {
                                            $item->filepages = null; // Set to null if there's an error
                                        }
                                    }
                                }
                                $item->message = $basePath . $item->message;
                            }
                            if (in_array($item->reply_type, ['image', 'document', 'audio', 'video'])) {
                                $item->reply_message = $basePath . $item->reply_message;
                            }
                            $reply_is_me=($item->reply_user_id==auth()->id()) ? true :false;
                            // Prepare reply details array
                            $item->reply_details = [
                                'message_id' => $item->reply_message_id,
                                'message' => $item->reply_message,
                                'type' => $item->reply_type,
                                'name' => $item->reply_user_name,
                                'phone_number' => $item->reply_user_phone_number,
                                'reply_is_me'=>$reply_is_me
                            ];
                            
                           
                            // Remove temporary fields for clarity in the final output
                            unset($item->reply_message_id, $item->reply_message, $item->reply_user_name, $item->reply_user_phone_number);

                        
                        
                            return $item;
                        });
                        
                        $message = "Message sent successfully";
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => $message,
                            'data' => $data,
                            'token'=>$fcmTokens
                        ], 200);
                    
                    }
                    else{
                        
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not an member',
                        ], 400);
                    }
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }               

            }else{
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401); // 500 Internal Server Error

            }
            

        } catch (\Exception $e) {
            // Handle the exception and return a generic error response
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while searching for groups.',
                'error' => $e->getMessage(), // For debugging; can be omitted in production
            ], 500); // 500 Internal Server Error
        }       

    }
    
    public function sendMultiplePushNotifications($fcm_tokens,$group_id,$group_name,$group_message,$type,$time)
    {
        
        $fcm_token=$fcm_tokens;
        $title = 'Info91';
        $body = $group_name.' : '.$group_message??'';
        $data = [
            'click_action' => 'FLUTTER_NOTIICATION_CLICK',
            'message' => $group_message ?? null,
            'group_id' => $group_id ?? null,
            'group_name' => $group_name ?? null,
            'type'=>$type ?? null,
            'time'=>$time ?? null,
            'sender_name'=>auth()->user()->name ?? auth()->user()->phone_number
        ];
        
        // Log::info('token: ', $fcm_token);
        foreach($fcm_token as $token){
           $response = $this->sendNotification($token, $title, $body, $data);
            $responses[] = $response;
            
        }

        //return $responses;
        return response()->json([
            'message' => 'Notifications sent successfully!',
            'responses' => $responses // Return all the responses from the notifications
        ]);
    } 
    public function viewGroupChat(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'page' => 'integer|min:1', // Page number to track pagination
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
    
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->exists();
    
                    if ($isUserInGroup) {
                        $userdata = DB::table($groupTable)
                            ->where('user_id', auth()->user()->id)
                            ->where('status', '1')
                            ->select('role', 'status')
                            ->first();
    
                        $groupmessageTable = 'group_message_' . $request->group_id;
    
                        // Define limit and calculate offset based on page number
                        $limit = 20;
                        $page = $request->input('page', 1);  // Default to first page if not provided
    
                        // Get total count of messages
                        $totalMessages = DB::table($groupmessageTable)->count();
                        // Calculate offset for the current page
                        $offset = max(0, $totalMessages - ($page * $limit));
    
                        // Fetch messages with calculated offset and limit
                        $isAdmin = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                        if($isAdmin===true){
                            $userList = DB::table($groupTable)
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();
                        }else{
                            $userList = DB::table($groupTable)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();

                        }
                        $authUser = (object) [
                            'user_id' => auth()->id(),
                        ];
                        $userList->push($authUser);
                        $userList = $userList->unique('user_id');
                        $userListIds = $userList->pluck('user_id')->toArray();    
    
                        $data = DB::table($groupmessageTable)
                            ->join("users", $groupmessageTable . '.user_id', '=', 'users.id')
                            ->leftJoin($groupmessageTable . ' as replies', $groupmessageTable . '.reply_message_id', '=', 'replies.id')
                            ->leftJoin('users as reply_users', 'replies.user_id', '=', 'reply_users.id')
                            ->select(
                                $groupmessageTable . '.id as message_id',
                                $groupmessageTable . '.user_id',
                                $groupmessageTable . '.type as type',
                                $groupmessageTable . '.message',
                                $groupmessageTable . '.message_status',
                                $groupmessageTable . '.created_at',
                                $groupmessageTable . '.reply_flag',
                                $groupmessageTable . '.reaction_flag',
                                $groupmessageTable . '.fwd_flag',
                                $groupmessageTable . '.download_users',
                                $groupmessageTable . '.deleted_users',
                                'users.image',
                                'users.phone_number',
                                'users.name',
                                'replies.id as reply_message_id',
                                'replies.message as reply_message',
                                'replies.user_id as reply_user_id',
                                'replies.type as reply_type',
                                'reply_users.name as reply_user_name',
                                'reply_users.phone_number as reply_user_phone_number'
                            )
                            ->whereIn($groupmessageTable . '.user_id', $userListIds)
                            ->orderBy('created_at', 'desc')
                            // ->limit(100)
                            ->get();
                    
                            // Map to format each item with full image URL and a structured `reply_details` array
                            $authUserId = '@' . auth()->user()->id;
                            
                            $data = $data->map(function ($item) use ($authUserId) {
                                
                                $downloadUsers = $item->download_users ? explode(',', $item->download_users) : [];
                                $item->file_download_flag = in_array($authUserId, $downloadUsers);
                            
                                // Check if the user's ID is in the `deleted_users` column
                                $deletedUsers = $item->deleted_users ? explode(',', $item->deleted_users) : [];
                                $item->file_deleted_flag = in_array($authUserId, $deletedUsers);

                            $item->image = env('APP_URL') . '/public/profile_pic/' . ($item->image ?? 'default.jpg');
                            $messageContent = $item->message;
                            $item->contact_list=[];
                            if ($item->type === 'contact') {
                                $item->message=null;
                                $item->contact_list = json_decode($messageContent); // Convert JSON data to string
                            } 
                            $item->reply_flag = (bool)$item->reply_flag; // Ensure this is set correctly
                            $item->fwd_flag = (bool)$item->fwd_flag; // Ensure this is set correctly
                            $item->reaction_flag = (bool)$item->reaction_flag; 
                            $createdAt = Carbon::parse($item->created_at);
                            $item->date = $createdAt->format('Y-m-d'); // Format date (e.g., 2024-11-06)
                            $item->time = $createdAt->format('h:i A');
                            $item->is_me = ($item->user_id==auth()->id()) ? true :false;
                            $basePath = env('APP_URL') . '/public/upload_files/';
                            $item->filesize=null;
                            $item->filename=null;
                            $item->filetype=null;
                            $item->filepages=0;
                            if (in_array($item->type, ['image', 'document', 'audio', 'video'])) {
                                $item->filename=$item->message;
                                $item->filetype = pathinfo($item->filename, PATHINFO_EXTENSION);
                                 $filePath = public_path('upload_files/' . $item->filename);
                                if (file_exists($filePath)) {
                                    $item->filesize = filesize($filePath); 
                                    $item->filesize = round(filesize($filePath) / 1024 / 1024, 2) . ' MB';
                                }
                                if (in_array(strtolower($item->filetype), ['pdf'])) {
                                   
                                
                                    if (file_exists($filePath)) {
                                        try {
                                            $imagick = new \Imagick();
                                            $imagick->pingImage($filePath);
                                            $item->filepages = $imagick->getNumberImages();
                                        } catch (\Exception $e) {
                                            $item->filepages = null; // Set to null if there's an error
                                        }
                                    }
                                }
                                $item->message = $basePath . $item->message;
                            }
                            if (in_array($item->reply_type, ['image', 'document', 'audio', 'video'])) {
                                $item->reply_message = $basePath . $item->reply_message;
                            }
                            $reply_is_me=($item->reply_user_id==auth()->id()) ? true :false;
                            // Prepare reply details array
                            $item->reply_details = [
                                'message_id' => $item->reply_message_id,
                                'message' => $item->reply_message,
                                'type' => $item->reply_type,
                                'user_id' => $item->reply_user_id,
                                'name' => $item->reply_user_name,
                                'phone_number' => $item->reply_user_phone_number,
                                'reply_is_me'=>$reply_is_me
                            ];
                           
                            // Remove temporary fields for clarity in the final output
                            unset($item->reply_message_id, $item->reply_message);

                            return $item;
                        });
                        // $data['total_messages'] = $totalMessages;
    
                        $message = "Message fetched successfully";
                        return response()->json([
                            'status' => 'success',
                            'statusCode' => 200,
                            'message' => $message,
                            'total_messages;' => $totalMessages,
                            'data' => $data
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 300,
                            'message' => 'Invalid User',
                        ], 300);
                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 300,
                        'message' => $message,
                        'data' => []
                    ], 300);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

 public function groupProfilePicUpdate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'image' => 'file|mimes:jpeg,jpg,png'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;
                if (Schema::hasTable($groupTable)) {
                    $is_member=DB::table($groupTable)->where('user_id',auth()->user()->id)->exists();
                    $is_admin_qry = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('role','1')
                        ->where('status','1')
                        ->exists();
                    // $is_admin=false;
                    if ($is_admin_qry===true) {
                        //$is_admin=false;
                        // Store the uploaded image in a folder and generate file name
                        $members = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id') // Joining users table
                            ->select(
                                $groupTable . '.user_id',
                                $groupTable . '.role',
                                $groupTable . '.status',
                                'users.phone_number', // Selecting user's phone number
                                'users.name', // Selecting user's phone number
                                'users.image',
                                'users.about', // Selecting user's phone number
                                'users.id' // Selecting user's id, aliasing to avoid conflict
                            )
                            ->where($groupTable . '.status', '=', '1') 
                            ->orderBy($groupTable . '.role', 'desc')
                            ->get()->map(function ($member) {
                                // Add full URL for member image
                                $member->image = env('APP_URL') . '/public/profile_pic/' . $member->image;
                                return $member;
                            });

                        $file = $request->file('image');
                        $fileExtension = $file->getClientOriginalExtension();
                        $fileName = auth()->user()->id . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $fileExtension;
                        
                        $file->move(public_path('group_profile_pic'), $fileName);

                        // Update the user's profile with the image path
                        DB::table('groups')->where('id', $request->group_id)->update([
                            'profile_image'   => $fileName,
                        ]);
                        $data  =DB::table('groups')
                                ->where('id', $request->group_id)->first();
                        $tempPro = $data->profile_image;
                        $data->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                        $tempCov = $data->cover_image;
                        $data->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                        $message="Image updated successfully!.";
                        $data->is_admin=$is_admin_qry;
                        $total_member_count = DB::table($groupTable)->where('status','1')->count();
                        $data->member_flag=$is_member;
                        $data->members_count = $total_member_count;
                        $data->members=$members;
                        $banners = DB::table($bannerTable)->get()->map(function($banner) {
                        $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                            return $banner;
                        });
                        $data->banners=$banners;
                        $data->media_list=$this->listGroupMedia($request->group_id);
                        return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$data,'success' => 'success'], $this-> successStatus);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not admin',
                        ], 400);
                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   
    public function groupCoverPicUpdate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'image' => 'file|mimes:jpeg,jpg,png'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;
                if (Schema::hasTable($groupTable)) {
                    $is_member=DB::table($groupTable)->where('user_id',auth()->user()->id)->exists();
                    $is_admin_qry = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('role','1')
                        ->where('status','1')
                        ->exists();
                        // $is_admin=false;
                    if ($is_admin_qry===true) {
                        // $is_admin=true;
                        // Store the uploaded image in a folder and generate file name
                        
                        $members = DB::table($groupTable)
                            ->join('users', $groupTable . '.user_id', '=', 'users.id') // Joining users table
                            ->select(
                                $groupTable . '.user_id',
                                $groupTable . '.role',
                                $groupTable . '.status',
                                'users.phone_number', // Selecting user's phone number
                                'users.name', // Selecting user's phone number
                                'users.image',
                                'users.about', // Selecting user's phone number
                                'users.id' // Selecting user's id, aliasing to avoid conflict
                            )
                            ->where($groupTable . '.status', '=', '1') 
                            ->orderBy($groupTable . '.role', 'desc')
                            ->get()->map(function ($member) {
                                // Add full URL for member image
                                $member->image = env('APP_URL') . '/public/profile_pic/' . $member->image;
                                return $member;
                            });
                        $file = $request->file('image');
                        $fileExtension = $file->getClientOriginalExtension();
                        $fileName = auth()->user()->id . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $fileExtension;

                        $file->move(public_path('group_cover_pic'), $fileName);

                        // Update the user's profile with the image path
                        DB::table('groups')->where('id', $request->group_id)->update([
                            'cover_image'   => $fileName,
                        ]);
                        $data  =DB::table('groups')
                                ->where('id', $request->group_id)->first();
                        $tempPro = $data->profile_image;
                        $data->profile_image=env('APP_URL').'/public/group_profile_pic/'.$tempPro;
                        $tempCov = $data->cover_image;
                        $data->cover_image=env('APP_URL').'/public/group_cover_pic/'.$tempCov;
                        $message="Image updated successfully!.";
                        $data->is_admin=$is_admin_qry;
                        $total_member_count = DB::table($groupTable)->where('status','1')->count();
                        $data->member_flag=$is_member;
                        $data->members_count = $total_member_count;
                        $data->members=$members;
                        $banners = DB::table($bannerTable)->get()->map(function($banner) {
                        $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                            return $banner;
                        });
                        $data->banners=$banners;
                        $data->media_list=$this->listGroupMedia($request->group_id);
                        return response()->json(['message'=>$message, 'statusCode' => $this-> successStatus,'data'=>$data,'success' => 'success'], $this-> successStatus);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not admin',
                        ], 400);
                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteGroupMessage(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'message_id' => 'required|array', // Expecting an array of message IDs
                'message_id.*' => 'string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
    
                if (Schema::hasTable($groupTable)) {
                    $isUserInGroup = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('status', '1')
                        ->where('role', '1')
                        ->exists();
    
                    if ($isUserInGroup) {

                        $groupmessageTable = 'group_message_' . $request->group_id;
                        $deletedCount = 0;

                        // Loop through each message ID and delete individually
                        foreach ($request->message_id as $messageId) {
                            $deleted = DB::table($groupmessageTable)
                                ->where('id', $messageId)
                                ->delete();

                            if ($deleted) {
                                $deletedCount++;
                            }
                        }
                        $isAdmin = DB::table($groupTable)
                        ->where('user_id',auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();
                        if($isAdmin===true){
                            $userList = DB::table($groupTable)
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();
                        }else{
                            $userList = DB::table($groupTable)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();

                        }
                        $authUser = (object) [
                            'user_id' => auth()->id(),
                        ];
                        $userList->push($authUser);
                        $userList = $userList->unique('user_id');
                        $userListIds = $userList->pluck('user_id')->toArray();    
    
                        $data = DB::table($groupmessageTable)
                            ->join("users", $groupmessageTable . '.user_id', '=', 'users.id')
                            ->leftJoin($groupmessageTable . ' as replies', $groupmessageTable . '.reply_message_id', '=', 'replies.id')
                            ->leftJoin('users as reply_users', 'replies.user_id', '=', 'reply_users.id')
                            ->select(
                                $groupmessageTable . '.id as message_id',
                                $groupmessageTable . '.user_id',
                                $groupmessageTable . '.type as type',
                                $groupmessageTable . '.message',
                                $groupmessageTable . '.message_status',
                                $groupmessageTable . '.created_at',
                                $groupmessageTable . '.reply_flag',
                                $groupmessageTable . '.reaction_flag',
                                'users.image',
                                'users.phone_number',
                                'users.name',
                                'replies.id as reply_message_id',
                                'replies.message as reply_message',
                                'replies.type as reply_type',
                                'reply_users.name as reply_user_name',
                                'reply_users.phone_number as reply_user_phone_number'
                            )
                            ->whereIn($groupmessageTable . '.user_id', $userListIds)
                            ->orderBy('created_at', 'desc')
                            // ->limit(100)
                            ->get();
                    
                            // Map to format each item with full image URL and a structured `reply_details` array
                            $data = $data->map(function ($item) {
                            // Set the main image URL
                            $item->image = env('APP_URL') . '/public/profile_pic/' . ($item->image ?? 'default.jpg');
                            $messageContent = $item->message;
                            $item->contact_list=[];
                            if ($item->type === 'contact') {
                                $item->message=null;
                                $item->contact_list = json_decode($messageContent); // Convert JSON data to string
                            } 
                            $item->reply_flag = (bool)$item->reply_flag; // Ensure this is set correctly
                            $item->reaction_flag = (bool)$item->reaction_flag; 
                            $createdAt = Carbon::parse($item->created_at);
                            $item->date = $createdAt->format('Y-m-d'); // Format date (e.g., 2024-11-06)
                            $item->time = $createdAt->format('h:i A');
                            $item->is_me = ($item->user_id==auth()->id()) ? true :false;
                            $basePath = env('APP_URL') . '/public/upload_files/';
                            if (in_array($item->type, ['image', 'document', 'audio', 'video'])) {
                                $item->message = $basePath . $item->message;
                            }
                            if (in_array($item->reply_type, ['image', 'document', 'audio', 'video'])) {
                                $item->reply_message = $basePath . $item->reply_message;
                            }
                            // Prepare reply details array
                            $item->reply_details = [
                                'message_id' => $item->reply_message_id,
                                'message' => $item->reply_message,
                                'type' => $item->reply_type,
                                'name' => $item->reply_user_name,
                                'phone_number' => $item->reply_user_phone_number
                            ];
                           
                            // Remove temporary fields for clarity in the final output
                            unset($item->reply_message_id, $item->reply_message, $item->reply_user_name, $item->reply_user_phone_number);

                            return $item;
                        });
                        
                        if ($deletedCount > 0) {
                            return response()->json([
                                'status' => 'success',
                                'statusCode' => 200,
                                'message' => "$deletedCount message(s) deleted successfully",
                                'data' => $data
                            ], 200);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'No messages found or deletion failed',
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not Admin',
                        ], 400);
                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addGroupBanner(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'banner_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'banner_title' => 'required|string',
                'banner_description' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;
                if (Schema::hasTable($groupTable)) {
                    $isAdmin = DB::table($groupTable)
                            ->where('user_id',auth()->user()->id)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->exists();
                    if($isAdmin===true){
                        $id=$this->generateId();
                        $bannerData=[
                            "id"=>$id,
                            // "image"=>$request->image,
                            "title"=>$request->banner_title,
                            "description"=>$request->banner_description,
                            "status"=>'1',
                        ];

                        if ($request->hasFile('banner_image')) {
                            $image = $request->file('banner_image');
                    
                            // Generate a unique file name with the original extension
                            $imageName = $id.time() . '.' . $image->getClientOriginalExtension();
                    
                            // Move the file to the public/images directory
                            $image->move(public_path('group_banner_images'), $imageName);
                    
                            // Save the file name in the banner data
                            $bannerData['image'] = $imageName;
                        }
                        $insertedId = DB::table($bannerTable)->insertGetId($bannerData);
                        $banners = DB::table($bannerTable)->get()->map(function($banner) {
                            $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                            return $banner;
                        });
                        // Return a success response with the inserted ID
                        return response()->json([
                            'success' => true,
                            'message' => 'Banner created successfully',
                            'data' => [
                                'banners' => $banners
                            ]
                        ], 200);

                    }else{
                        $message = "You are not Admin";
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => $message,
                            'data' => []
                        ], 400);

                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }

            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteGroupBanner(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'banner_id' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }
    
            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;
                if (Schema::hasTable($groupTable)) {
                    $isAdmin = DB::table($groupTable)
                            ->where('user_id',auth()->user()->id)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->exists();
                    if($isAdmin===true){
                        
                        $banner = DB::table($bannerTable)->where('id', $request->banner_id)->first();

                        if ($banner) {
                            // Delete the banner image file from storage if it exists
                            $imagePath = public_path('group_banner_images/' . $banner->image);
                            if (file_exists($imagePath)) {
                                unlink($imagePath); // Remove the image file
                            }
    
                            // Delete the banner record from the database
                            DB::table($bannerTable)->where('id', $request->banner_id)->delete();
    
                            // Retrieve updated list of banners
                            $banners = DB::table($bannerTable)->get()->map(function($banner) {
                                $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                                return $banner;
                            });
    
                            // Return a success response with the updated banners
                            return response()->json([
                                'success' => true,
                                'message' => 'Banner removed successfully',
                                'data' => [
                                    'banners' => $banners
                                ]
                            ], 200);
                        } else {
                            return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'Banner not found',
                                'data' => []
                            ], 400);
                        }

                    }else{
                        $message = "You are not Admin";
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => $message,
                            'data' => []
                        ], 400);

                    }
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }

            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while fetching messages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateGroupBanner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required|string',
                'banner_id' => 'required|string',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image file
                'banner_title' => 'required|string', // Any additional fields for updating
                'banner_description' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'statusCode' => 422,
                    'data' => [],
                    'success' => 'error',
                ], 422);
            }

            if (auth()->user()) {
                $groupTable = 'group_users_' . $request->group_id;
                $bannerTable = 'group_banners_' . $request->group_id;

                if (Schema::hasTable($groupTable)) {
                    $isAdmin = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();

                    if ($isAdmin) {
                        // Retrieve existing banner data
                        $banner = DB::table($bannerTable)->where('id', $request->banner_id)->first();
                        if (!$banner) {
                            return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'Banner not found',
                                'data' => []
                            ], 400);
                        }

                        $bannerData = [];
                        
                        // Handle image upload if a new image is provided
                        if ($request->hasFile('banner_image')) {
                            $image = $request->file('banner_image');
                            $imageName = $banner->id.time() . '.' . $image->getClientOriginalExtension();
                            $image->move(public_path('group_banner_images'), $imageName);

                            // Delete the old image file
                            $oldImagePath = public_path('group_banner_images/' . $banner->image);
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }

                            // Update banner image path
                            $bannerData['image'] = $imageName;
                        }

                        // Update other fields
                        if ($request->filled('banner_title')) {
                            $bannerData['title'] = $request->banner_title;
                        }
                        if ($request->filled('banner_description')) {
                            $bannerData['description'] = $request->banner_description;
                        }

                        // Update the banner in the database
                        if (!empty($bannerData)) {
                            DB::table($bannerTable)->where('id', $request->banner_id)->update($bannerData);
                        } else {
                            // Handle case where there's nothing to update
                            return response()->json([
                                'success' => false,
                                'statusCode' => 400,
                                'message' => 'No update data provided',
                            ], 400);
                        }

                        // Retrieve the updated list of banners
                        $banners = DB::table($bannerTable)->get()->map(function ($banner) {
                            $banner->image = url('group_banner_images/' . $banner->image); // Set full URL for image path
                            return $banner;
                        });

                        // Return success response with updated banners
                        return response()->json([
                            'success' => true,
                            'message' => 'Banner updated successfully',
                            'data' => [
                                'banners' => $banners
                            ]
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'You are not Admin',
                            'data' => []
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => 'Invalid Group',
                        'data' => []
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while updating the banner.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
  
    public function listGroupMedia($group_id){       

        if (auth()->user()) {
            $groupTable = 'group_users_' . $group_id;
            $groupmessageTable = 'group_message_' . $group_id;

            if (Schema::hasTable($groupTable)) {
                $isMember = DB::table($groupTable)
                    ->where('user_id', auth()->user()->id)
                    ->where('status', '1')
                    ->exists();

                if ($isMember) {
                    $isAdmin = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('role', '1')
                        ->where('status', '1')
                        ->exists();

                    if ($isAdmin === true) {
                        $userList = DB::table($groupTable)
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();
                    } else {
                        $userList = DB::table($groupTable)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->select('user_id')
                            ->get();
                    }

                    // Add the authenticated user
                    $authUser = (object) [
                        'user_id' => auth()->id(),
                    ];
                    $userList->push($authUser);
                    $userList = $userList->unique('user_id');
                    $userListIds = $userList->pluck('user_id')->toArray();

                    // Fetch the messages from the database
                    $data = DB::table($groupmessageTable)
                        ->join("users", $groupmessageTable . '.user_id', '=', 'users.id')
                        ->select(
                            $groupmessageTable . '.id as message_id',
                            $groupmessageTable . '.user_id',
                            $groupmessageTable . '.type as type',
                            $groupmessageTable . '.message',
                            $groupmessageTable . '.created_at',
                            'users.phone_number',
                            'users.name'
                        )
                        ->whereIn($groupmessageTable . '.user_id', $userListIds)
                        ->whereIn($groupmessageTable . '.type', ['image', 'document', 'audio', 'video'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    // Initialize separate lists for each media type
                    $imageList = [];
                    $audioList = [];
                    $videoList = [];
                    $documentList = [];

                    // Process and map data
                    $data = $data->map(function ($item) use (&$imageList, &$audioList, &$videoList, &$documentList) {
                        $createdAt = Carbon::parse($item->created_at);
                        $item->date = $createdAt->format('Y-m-d');
                        $item->time = $createdAt->format('h:i A');
                        $item->is_me = ($item->user_id == auth()->id()) ? true : false;
                        $basePath = env('APP_URL') . '/public/upload_files/';
                        $item->filesize = null;
                        $item->filename = null;
                        $item->filetype = null;
                        $item->filepages = 0;

                        // Process file information
                        if (in_array($item->type, ['image', 'document', 'audio', 'video'])) {
                            $item->filename = $item->message;
                            $item->filetype = pathinfo($item->filename, PATHINFO_EXTENSION);
                            $filePath = public_path('upload_files/' . $item->filename);

                            if (file_exists($filePath)) {
                                $item->filesize = filesize($filePath);
                                $item->filesize = round(filesize($filePath) / 1024 / 1024, 2) . ' MB';
                            }

                            if (in_array(strtolower($item->filetype), ['pdf'])) {
                                if (file_exists($filePath)) {
                                    try {
                                        $imagick = new \Imagick();
                                        $imagick->pingImage($filePath);
                                        $item->filepages = $imagick->getNumberImages();
                                    } catch (\Exception $e) {
                                        $item->filepages = null;
                                    }
                                }
                            }

                            $item->message = $basePath . $item->message;

                            // Categorize by type
                            switch ($item->type) {
                                case 'image':
                                    $imageList[] = $item;
                                    break;
                                case 'audio':
                                    $audioList[] = $item;
                                    break;
                                case 'video':
                                    $videoList[] = $item;
                                    break;
                                case 'document':
                                    $documentList[] = $item;
                                    break;
                            }
                        }

                        return $item;
                    });

                    $result = [
                        'image_list' => $imageList,
                        'video_list' => $videoList,
                        'audio_list' => $audioList,
                        'document_list' => $documentList
                    ];
                    return $result;
                } else {
                    return $result = ['image_list' => null,
                        'video_list' => null,
                        'audio_list' => null,
                        'document_list' => null];
                }
            } else {
                 return $result = ['image_list' => null,
                        'video_list' => null,
                        'audio_list' => null,
                        'document_list' => null];
            }
        } else {
            return $result = ['image_list' => null,
                        'video_list' => null,
                        'audio_list' => null,
                        'document_list' => null];
        }
       
    }

   
    
    public function addUsersToGroup(Request $request){       
        try{
            if (auth()->user()) {
                $validator = Validator::make($request->all(), [
                    'user_ids' => 'required|array',
                    'group_id' => 'required|exists:groups,id',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'message' => $validator->errors(),
                        'statusCode' => 422,
                        'data' => [],
                        'success' => 'error',
                        "response"=>$request
                    ], 422);
                }
                $groupTable = 'group_users_' . $request->group_id;

                if (Schema::hasTable($groupTable)) {
                    $isMember = DB::table($groupTable)
                        ->where('user_id', auth()->user()->id)
                        ->where('status', '1')
                        ->exists();

                    if ($isMember) {
                        $isAdmin = DB::table($groupTable)
                            ->where('user_id', auth()->user()->id)
                            ->where('role', '1')
                            ->where('status', '1')
                            ->exists();

                        if ($isAdmin === true) {                           
                            $userIds = $request->user_ids;
                            $data = [];
                            foreach ($userIds as $userId) {
                                $userExist = DB::table($groupTable)
                                    ->where('user_id', $userId)
                                    ->exists();
                            
                                if ($userExist===true) {
                                    // Update the `status` to '1' for the existing record
                                    DB::table($groupTable)
                                        ->where('user_id', $userId)
                                        ->update(['status' => '1']);
                                } else {
                                    // Prepare the data for insertion
                                    $data = [
                                        'user_id' => $userId,
                                        'alarm_status' => '1',
                                        'status' => '1',
                                    ];
                                    // Insert the new record
                                    DB::table($groupTable)->insert($data);
                                }
                            }
  
                            return response()->json([
                                'success' => true,
                                'statusCode' => 200,
                                'message' => 'Memebers added successfully',
                                'data' => []
                            ], 200);
                        }else{
                            return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'You are not Admin',
                                'data' => []
                            ], 400);
                        }
                    }else{
                        return response()->json([
                                'status' => 'error',
                                'statusCode' => 400,
                                'message' => 'You are an member',
                                'data' => []
                            ], 400);
                    }
                }else{
                    return response()->json([
                            'status' => 'error',
                            'statusCode' => 400,
                            'message' => 'Invalid Group',
                            'data' => []
                        ], 400);
                }    
            }else{
                return response()->json([
                        'status' => 'error',
                        'statusCode' => 401,
                        'message' => 'Authentication failed',
                    ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while updating the banner.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   
    
    public function forwardMessage(Request $request)
    {
        try {
            // Validate input data
            $validator = Validator::make($request->all(), [
                'frm_group_user_id' => 'required|string',
                'group_ids' => 'required|array|min:1',
                'message_ids' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 422,
                    'message' => $validator->errors(),
                    'data' => [],
                ], 422);
            }

            // Check user authentication
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed.',
                ], 401);
            }

            $successGroups = [];
            $failedGroups = [];
            $fromgroupMessageTable = 'group_message_' . $request->frm_group_user_id;

            foreach ($request->group_ids as $groupId) {
                $groupTable = 'group_users_' . $groupId;
                $groupMessageTable = 'group_message_' . $groupId;

                // Check if the user is a member of the group
                $isMember = DB::table($groupTable)
                    ->where('user_id', $authUser->id)
                    ->where('status', '1')
                    ->exists();

                if (!$isMember) {
                    $failedGroups[] = $groupId;
                    continue;
                }

                // Retrieve original messages and prepare for insertion
                $messagesToForward = [];
                foreach ($request->message_ids as $messageId) {
                    $originalMessage = DB::table($fromgroupMessageTable)
                        ->where('id', $messageId)
                        ->first();

                    if ($originalMessage) {
                        $messagesToForward[] = [
                            'id' => $this->generateId(),
                            'user_id' => $authUser->id,
                            'type' => $originalMessage->type,
                            'message' => $originalMessage->message,
                            'reply_flag' => '0',
                            'reply_message_id' => null,
                            'fwd_flag' => '1',
                            'fwd_group_user_id' => $request->frm_group_user_id,
                            'message_status' => 'Send',
                            'status' => '1',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Batch insert messages
                if (!empty($messagesToForward)) {
                    DB::table($groupMessageTable)->insert($messagesToForward);
                    $successGroups[] = $groupId;
                } else {
                    $failedGroups[] = $groupId;
                }
            }

            // Prepare response
            $status = empty($failedGroups) ? 'success' : 'error';
            $statusCode = empty($failedGroups) ? 200 : 400;
            $message = empty($failedGroups)
                ? 'Message forwarding completed successfully.'
                : 'Some messages failed to forward.';

            $data = [
                'success_groups' => $successGroups,
                'failed_groups' => $failedGroups,
            ];

            return response()->json([
                'status' => $status,
                'statusCode' => $statusCode,
                'message' => $message,
                'data' => $data,
            ], $statusCode);

        } catch (\Exception $e) {
            // Log the exception (optional)
            Log::error('Error forwarding messages:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while forwarding messages.',
            ], 500);
        }
    }

    
    public function downloadFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|string',
            'message_ids' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }

        try {
            if (!auth()->user()) {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed.',
                ], 401);
            }

            $authUserId = '@' . auth()->user()->id;
            $successFiles = [];
            $failedFiles = [];

            $groupTable = 'group_message_' . $request->group_id;

            if (Schema::hasTable($groupTable)) {
                foreach ($request->message_ids as $messageId) {
                    // Retrieve the current row
                    $message = DB::table($groupTable)->where('id', $messageId)->first();

                    if ($message) {
                        // Concatenate the user's ID to the download_users column
                        $updatedDownloadUsers = $message->download_users
                            ? $message->download_users . ',' . $authUserId
                            : $authUserId;

                        // Update the record
                        DB::table($groupTable)
                            ->where('id', $messageId)
                            ->update(['download_users' => $updatedDownloadUsers]);

                        $successFiles[] = $messageId;
                    } else {
                        $failedFiles[] = $messageId;
                    }
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 400,
                    'message' => 'Invalid group.',
                ], 400);
            }

            $message = empty($failedFiles)
                ? 'Files processed successfully.'
                : 'Some files could not be processed.';

            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => [
                    'success_files' => $successFiles,
                    'failed_files' => $failedFiles,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging (optional)
            Log::error('Error in downloadFiles:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while processing the files.',
            ], 500);
        }
    }


    public function removeFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|string',
            'message_ids' => 'required|array|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'statusCode' => 422,
                'data' => [],
                'success' => 'error',
            ], 422);
        }
    
        try {
            if (!auth()->user()) {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed.',
                ], 401);
            }
    
            $authUserId = '@' . auth()->user()->id;
            $successFiles = [];
            $failedFiles = [];
    
            $groupTable = 'group_message_' . $request->group_id;
    
            if (Schema::hasTable($groupTable)) {
                foreach ($request->message_ids as $messageId) {
                    // Retrieve the current row
                    $message = DB::table($groupTable)->where('id', $messageId)->first();
    
                    if ($message) {
                        // Concatenate the user's ID to the deleted_users column
                        $updatedDeletedUsers = $message->deleted_users
                            ? $message->deleted_users . ',' . $authUserId
                            : $authUserId;
    
                        // Update the record
                        DB::table($groupTable)
                            ->where('id', $messageId)
                            ->update(['deleted_users' => $updatedDeletedUsers]);
    
                        $successFiles[] = $messageId;
                    } else {
                        $failedFiles[] = $messageId;
                    }
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 400,
                    'message' => 'Invalid group.',
                ], 400);
            }
    
            $message = empty($failedFiles)
                ? 'Files removed successfully.'
                : 'Some files could not be removed.';
    
            return response()->json([
                'status' => 'success',
                'statusCode' => 200,
                'message' => $message,
                'data' => [
                    'success_files' => $successFiles,
                    'failed_files' => $failedFiles,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging (optional)
            Log::error('Error in removeFiles:', ['error' => $e->getMessage()]);
    
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while processing the files.',
            ], 500);
        }
    }
     public function contactSyncOld(Request $request)
    {
        try {
            if (auth()->user()) {
               // Log::info('Contact Sync Request:', $request->all());
                $validator = Validator::make($request->all(), [
                    // 'contacts' => 'required|array',
                    'group_id' => 'required|exists:groups,id', // assuming `group_id` should exist in a `groups` table
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'message' => $validator->errors(),
                        'statusCode' => 422,
                        'data' => [],
                        'success' => 'error',
                        "response"=>$request
                    ], 422);
                }

                $contacts = $request->input('contacts', []);
                $groupId = $request->input('group_id');
                $groupTable = 'group_users_' . $groupId;
                
                if (Schema::hasTable($groupTable)) {
                    $processedContacts = [];
                    foreach ($contacts as $contact) {
                        if(!empty($contact['phones']))
                        {
                            foreach ($contact['phones'] as $phone) {
                                // Clean up and format the phone number
                                $trimmedNumber = preg_replace('/\s+/', '', $phone['number']);
                                $formattedNumber = substr($trimmedNumber, -10);
            
                                $processedContacts[] = [
                                    'original_number' => $phone['number'],
                                    'formatted_number' => $formattedNumber,
                                    'displayName' => $contact['displayName'] ?? null
                                ];
                            }
                            
                        }
                    }
        
                    // Retrieve existing phone numbers from users table
                    $formattedNumbers = array_column($processedContacts, 'formatted_number');
                    $existingUsers = User::whereIn('phone_number', $formattedNumbers)
                        ->get(['id','phone_number', 'name', 'image', 'about']);
        
                    // Prepare response data
                    $results = [];
                    foreach ($processedContacts as $contact) {
                        $number = $contact['formatted_number'];
                        $user = $existingUsers->firstWhere('phone_number', $number);
                        $group_exists = $user ? DB::table($groupTable)->where('user_id', $user->id)->exists() : false;
                        // Only attempt to set the profile image path if the user exists
                        $profileImage = $user && $user->image
                            ? env('APP_URL') . '/public/profile_pic/' . $user->image
                            : null;
        
                        $results[] = [
                            'original_number' => $contact['original_number'],
                            'formatted_number' => $number,
                            'displayName' => $contact['displayName'],
                            'exists' => $user ? true : false,
                            'group_exists' => $group_exists,
                            'name' => $user->name ?? null,
                            'about' => $user->about ?? null,
                            'profile_image' => $profileImage,
                            'user_id' => (string)($user->id ?? null),
                        ];
                    }
        
                    // Return the results
                    return response()->json([
                        'success' => true,
                        'message' => 'Contact sync successful',
                        'data' => $results
                    ], 200);
                }else{
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }      
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while syncing contacts.',
                'error' => $e->getMessage(),
                 "response"=>$request
            ], 500);
        }
    }
    public function contactSync(Request $request)
    {
        try {
            if (auth()->user()) {
                $validator = Validator::make($request->all(), [
                    'group_id' => 'required|exists:groups,id',
                ]);
    
                if ($validator->fails()) {
                    return response()->json([
                        'message' => $validator->errors(),
                        'statusCode' => 422,
                        'data' => [],
                        'success' => 'error',
                        "response" => $request
                    ], 422);
                }
    
                $contacts = $request->input('contacts', []);
                $groupId = $request->input('group_id');
                $groupTable = 'group_users_' . $groupId;
    
                if (Schema::hasTable($groupTable)) {
                    $processedContacts = [];
    
                    foreach ($contacts as $contact) {
                        $rawNumber = $contact['number'] ?? null;
                        $name = $contact['name'] ?? null;
    
                        if (!empty($rawNumber)) {
                            // Remove spaces and non-numeric characters
                            $trimmedNumber = preg_replace('/\D/', '', $rawNumber);
                            $formattedNumber = substr($trimmedNumber, -10); // Keep last 10 digits
    
                            $processedContacts[] = [
                                'original_number' => $rawNumber,
                                'formatted_number' => $formattedNumber,
                                'displayName' => $name ?? null
                            ];
                        }
                    }
    
                    $formattedNumbers = array_column($processedContacts, 'formatted_number');
                    // $existingUsers = User::whereIn('phone_number', $formattedNumbers)
                    //    ->get(['id', 'phone_number', 'name', 'image', 'about']);
                    $existingUsers = User::whereIn('phone_number', $formattedNumbers)
    ->first(['id', 'phone_number', 'name', 'image', 'about']);
    
                    $results = [];
                    foreach ($processedContacts as $contact) {
                        $number = $contact['formatted_number'];
                        $user = $existingUsers->firstWhere('phone_number', $number);
                        $group_exists = $user ? DB::table($groupTable)->where('user_id', $user->id)->exists() : false;
    
                        $profileImage = $user && $user->image
                            ? env('APP_URL') . '/public/profile_pic/' . $user->image
                            : null;
    
                        $results[] = [
                            'original_number' => $contact['original_number'],
                            'formatted_number' => $number,
                            'displayName' => $contact['displayName'],
                            'exists' => $user ? true : false,
                            'group_exists' => $group_exists,
                            'user_name' => $user->name ?? null,
                            'about' => $user->about ?? null,
                            'profile_image' => $profileImage,
                            'user_id' => (string)($user->id ?? null),
                        ];
                    }
                    usort($results, function ($a, $b) {
                        if ($a['exists'] === $b['exists']) {
                            return strcmp($a['user_name'] ?? '', $b['user_name'] ?? '');
                        }
                        return $a['exists'] ? -1 : 1;
                    });
                    return response()->json([
                        'success' => true,
                        'message' => 'Contact sync successful',
                        'data' => $results
                    ], 200);
                } else {
                    $message = "Invalid Group";
                    return response()->json([
                        'status' => 'error',
                        'statusCode' => 400,
                        'message' => $message,
                        'data' => []
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 401,
                    'message' => 'Authentication failed',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 500,
                'message' => 'An error occurred while syncing contacts.',
                'error' => $e->getMessage(),
                "response" => $request
            ], 500);
        }
    }


}