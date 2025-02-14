<?php

namespace App\Http\Controllers;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class GroupController extends Controller
{
    public function show($id)
    {
        $group = Group::with(['firstCategory', 'secondCategory', 'thirdCategory', 'plan'])
                    ->findOrFail($id)
                    ->makeHidden(['created_at']) // Optional: hide raw created_at
                    ->setAppends(['formatted_created_at']); // Append the formatted date

        return view('admin.group-approvals.show', compact('group'));
    }

    public function edit($id)
    {
        $group = Group::findOrFail($id);
        return view('group-approvals.edit', compact('group'));
    }
    public function rejectGroup($id)
    {
        try {
            
            $group = Group::findOrFail($id);
            $group->status = '2';  
            $group->save();

            return response()->json(['success' => 'Group rejected successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error rejecting group'], 500);
        }
    }

    public function idleGroup($id)
    {
        try {
            
            $group = Group::findOrFail($id);
            $group->status = '0';  
            $group->save();

            return response()->json(['success' => 'Group is idle now']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error to make idle group'], 500);
        }
    }

    public function approveGroup($id)
    {
        try {
            $tableName = 'group_users_' . $id;
            $groupMessagesTable = 'group_message_' . $id;
            $groupBannersTable = 'group_banners_' . $id;
    
            // Check and create the `group_message` table dynamically
            if (!Schema::hasTable($groupMessagesTable)) {
                Schema::create($groupMessagesTable, function (Blueprint $table) {
                    $table->string('id', 22)->primary();
                    $table->string('user_id', 22);
                    $table->enum('type', ['text', 'image', 'document', 'audio', 'video', 'contact', 'mention', 'reaction']);
                    $table->text('message')->nullable();
                    $table->enum('reply_flag', ['0', '1']);
                    $table->string('reply_message_id', 22)->nullable();
                    $table->enum('fwd_flag', ['0', '1']);
                    $table->enum('fwd_from_group_flag', ['0', '1']);
                    $table->string('fwd_group_user_id', 22)->nullable();
                    $table->string('fwd_message_id', 22)->nullable();
                    $table->enum('message_status', ['send', 'read', 'delivered'])->default('send');
                    $table->enum('reaction_flag', ['0', '1']);
                    $table->text('reacted_users')->nullable();
                    $table->text('download_users')->nullable();
                    $table->text('deleted_users')->nullable();
                    $table->enum('status', ['0', '1']);
                    $table->timestamps();
                });
            }
    
            // Check and create the `group_banners` table dynamically
            if (!Schema::hasTable($groupBannersTable)) {
                Schema::create($groupBannersTable, function (Blueprint $table) {
                    $table->string('id', 22)->primary();
                    $table->string('image', 256)->nullable();
                    $table->string('title', 256)->nullable();
                    $table->string('description', 500)->nullable();
                    $table->enum('status', ['0', '1'])->default('1');
                    $table->timestamps();
                });
            }
            if (!Schema::hasTable($tableName)) {
                // Create the table dynamically
                Schema::create($tableName, function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('user_id', 22)->nullable();
                    $table->enum('alarm_status', ['1', '2'])->default('1');
                    $table->enum('role', ['0', '1'])->default('0');
                    $table->enum('status', ['0', '1'])->default('1');
                    $table->timestamps();
                });
            }
            $group = Group::findOrFail($id);
            $group->status = '1';  
            $group->save();
            if (auth()->id()) {
                $exists = DB::table($tableName)
                    ->where('user_id', $group->created_by)
                    ->exists();
            
                if (!$exists) {
                    DB::table($tableName)->insert([
                        'user_id' => $group->created_by,
                        'alarm_status' => '1',
                        'role' => '1',
                        'status' => '1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            return response()->json(['success' => 'Group approved successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error approving group'], 500);
        }
    }



    public function grouplist(Request $request)
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
                'group_name',
                'type',
                'category1',
                'category2',
                'category3',
                'mobile_number',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = Group::with(['firstCategory', 'secondCategory', 'thirdCategory', 'plan'])->where('Status', '0')
                ->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('group_name', 'like', '%'.$searchValue.'%')
                        ->orWhere('mobile_number', 'like', '%'.$searchValue.'%')
                        ->orWhere('type', 'like', '%'.$searchValue.'%');
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
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\''.$row->id.'\',\'view\')" title="view"><i class="fas fa-eye"></i></button>
                       <button type="button" class="btn btn-danger btn-sm" onclick="handleAction(\''.$row->id.'\',\'reject\')" title="reject"><i class="fas fa-times"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="handleAction(\''.$row->id.'\',\'approve\')" title="approve"><i class="fas fa-check"></i></button>';

                return [
                    'id' => $row->id,
                    'group_name' => $row->group_name,
                    'type' => $row->type,
                    'purpose' => $row->purpose,
                    'mobile_number' => $row->mobile_number,
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),  // Date formatting
                    'action' => $action,

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

    public function approvedgrouplist(Request $request)
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
                'group_name',
                'type',
                'category1',
                'category2',
                'category3',
                'mobile_number',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = Group::with(['firstCategory', 'secondCategory', 'thirdCategory', 'plan'])->where('Status', '1')
                ->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('group_name', 'like', '%'.$searchValue.'%')
                        ->orWhere('mobile_number', 'like', '%'.$searchValue.'%')
                        ->orWhere('type', 'like', '%'.$searchValue.'%');
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

            $formattedData = $data->map(function($row) {
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\''.$row->id.'\',\'view\')" title="view"><i class="fas fa-eye"></i></button>
                       <button type="button" class="btn btn-warning btn-sm" onclick="handleAction(\''.$row->id.'\',\'idle\')" title="idle"><i class="fas fa-exclamation-circle"></i></button>
                     <button type="button" class="btn btn-danger btn-sm" onclick="handleAction(\''.$row->id.'\',\'reject\')" title="reject"><i class="fas fa-times"></i></button>';

                return [
                    'id' => $row->id,
                    'group_name' => $row->group_name,
                    'type' => $row->type,
                    'purpose' => $row->purpose,
                    'mobile_number' => $row->mobile_number,
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),  // Date formatting
                    'action' => $action,

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

    public function rejectedgrouplist(Request $request)
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
                'group_name',
                'type',
                'category1',
                'category2',
                'category3',
                'mobile_number',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = Group::with(['firstCategory', 'secondCategory', 'thirdCategory', 'plan'])->where('Status', '2')
                ->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('group_name', 'like', '%'.$searchValue.'%')
                        ->orWhere('mobile_number', 'like', '%'.$searchValue.'%')
                        ->orWhere('type', 'like', '%'.$searchValue.'%');
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

            $formattedData = $data->map(function($row) {
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\''.$row->id.'\',\'view\')" title="view"><i class="fas fa-eye"></i></button>
                     <button type="button" class="btn btn-warning btn-sm" onclick="handleAction(\''.$row->id.'\',\'idle\')" title="idle"><i class="fas fa-exclamation-circle"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="handleAction(\''.$row->id.'\',\'approve\')" title="approve"><i class="fas fa-check"></i></button>';

                return [
                    'id' => $row->id,
                    'group_name' => $row->group_name,
                    'type' => $row->type,
                    'purpose' => $row->purpose,
                    'mobile_number' => $row->mobile_number,
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),  // Date formatting
                    'action' => $action,

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


}
