<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class UserController extends Controller
{
    public function view($id)
    {
        $user = User::with([
            "pincodeDetails.district.state"
        ])->findOrFail($id); // Get a single user
    
        return view('admin.user.view', compact('user'));
    }
    

    public function edit($id)
    {
        try {
            $user = User::with([
                "pincodeDetails.district.state"
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    
    public function userList(Request $request)
    {  
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;

            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $columns = [
                'id',
                'name',
                'phone_number',
                'state_name',
                'district_name',
                'pincode',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';  
            $query = User::with(["pincodeDetails"])->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('name', 'like', '%'.$searchValue.'%')
                        ->orWhere('pincode', 'like', '%'.$searchValue.'%');
                });
            }

            $recordsTotal = $query->count();

            // Retrieve data with pagination
            $data = $query->skip($skip)->take($pageLength)->get();

            $recordsFiltered = $recordsTotal;
            // Check if data is empty
            if ($data->isEmpty()) {
                // return response()->json(['message' => 'No records found.'], 404);
                return response()->json([
                    "draw" => $request->draw,
                    "recordsTotal" => $recordsTotal,
                    "recordsFiltered" => $recordsFiltered,
                    'data' => [],
                ], 200);
            }

            // return $data;
            // Format the data to include action buttons
            $formattedData = $data->map(function($row) {
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\''.$row->id.'\',\'view\')" title="View"><i class="fas fa-eye"></i></button>';
               // <button type="button" class="btn btn-success btn-sm" onclick="editUser(\''.$row->id.'\')" title="Edit"><i class="fas fa-pen"></i></button>
                
                       
                       
                if($row->status=='1'){$status="Active";
                    //$action .= ' <button type="button" class="btn btn-danger btn-sm" onclick="handleAction(\''.$row->id.'\',\'ban\')" title="Ban"><i class="fas fa-ban"></i></button>';
                }else if($row->status=='0'){$status="Inactive";
                    //$action .= ' <button type="button" class="btn btn-warning btn-sm" onclick="handleAction(\''.$row->id.'\',\'inactive\')" title="Active"><i class="fas fa-check"></i></button>';
                }
                else{$status="Blocked";
                    //$action .= ' <button type="button" class="btn btn-default btn-sm" onclick="handleAction(\''.$row->id.'\',\'unban\')" title="Unban"><i class="fas fa-check"></i></button>';
                }
                return [
                    'id' => $row->id,
                    'user_id' => $row->id,
                    'name' => $row->name ?? 'N/A',
                    'phone_number' => $row->phone_number,
                    'district_name' => $row->pincodeDetails->district->district_name ?? 'N/A',
                    'state_name' => $row->pincodeDetails->district->state->state_name ?? 'N/A',
                    'pincode' => $row->pincode ?? 'N/A',
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),  // Date formatting
                    'status' => $status,
                    'action' => $action
                ];
            });

            return response()->json([
                "draw" => $request->draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                'data' => $formattedData,
            ], 200);
        }
    }

    
    public function updateUserStatus(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            
            $user->status = $request->status;
            $user->save();

            return response()->json(['message' => 'User status updated successfully',"success"=>true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
