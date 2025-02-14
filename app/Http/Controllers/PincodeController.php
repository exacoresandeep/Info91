<?php

namespace App\Http\Controllers;
use App\Models\Pincode;
use App\Models\State;
use App\Models\District;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // Import Excel library
use App\Imports\PincodeImport; // Your import class

class PincodeController extends Controller
{
    public function importPincode(Request $request)
    {
        // Validate input
        $request->validate([
            'district_id' => 'required|exists:districts,id',
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            // Import file
            Excel::import(new PincodeImport($request->district_id), $request->file('importFile'));

            return response()->json(['message' => 'Pincode data imported successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function pincodeList(Request $request)
    {    
        
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;

            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $state_id = $request->state;
            $district_id = $request->district_id;
            
            $columns = [
                'id',
                'postname',
                'pincode',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $query = Pincode::where('status', '!=', '2')
            ->when($district_id, function ($query) use ($district_id) {
                // If second category is provided, filter only by that
                return $query->where('district_id', $district_id);
            }, function ($query) use ($state_id) {
                // Otherwise, include all second categories under the selected first category
                return $query->whereHas('district', function ($query) use ($state_id) {
                    $query->where('state_id', $state_id);
                });
            })
            ->orderBy('status', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('postname', 'like', '%'.$searchValue.'%');
                });
            }

            $recordsTotal = $query->count();
            $data = $query->skip($skip)->take($pageLength)->get();
            $recordsFiltered = $recordsTotal;
            if ($data->isEmpty()) {
                return response()->json([
                    "draw" => $request->draw,
                    "recordsTotal" => $recordsTotal,
                    "recordsFiltered" => $recordsFiltered,
                    'data' => [],
                ], 200);
            }
            
            $formattedData = $data->map(function($row) {
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\''.$row->id.'\',\'view\')" title="view"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="editPincode(\''.$row->id.'\')" title="edit"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="deletePincode(\''.$row->id.'\')" title="delete"><i class="fas fa-trash"></i></button>';
                        if($row->status=='1'){$status="Active";}else if($row->status=='0'){$status="Inactive";}
                return [
                    'id' => $row->id,
                    'pincode' => $row->pincode,
                    'postname' => $row->postname,
                    'status' => $status,
                    'created_at' => $row->created_at? $row->created_at->format('Y-m-d H:i:s') : 'NA',  // Date formatting
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
    public function create()
    {
        $states = State::all(); 
        $districts = District::all(); 
        return view('admin.pincode.create', compact('states','districts'));
    }

    public function view($id)
    {
        $pincode = Pincode::find($id);

        if (!$pincode) {
            return response()->json(['success' => false, 'message' => 'Pincode not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.pincode.view', compact('pincode'))->render()
        ]);
    }

    public function delete($id)
    {
        $pincode = Pincode::find($id);

        if (!$pincode) {
            return response()->json(['success' => false, 'message' => 'Pincode not found.'], 404);
        }

        $pincode->delete();

        return response()->json(['success' => true, 'message' => 'Pincode deleted successfully.']);
    }

    public function edit($id) {
        $pincode = Pincode::find($id);
    
        if ($pincode) {
            return response()->json(['success' => true, 'data' => $pincode]);
        }
    
        return response()->json(['success' => false, 'message' => 'Pincode not found']);
    }
    
    public function update(Request $request, $id) {
        $pincode = Pincode::find($id);
    
        if ($pincode) {
            $pincode->postname = $request->input('postname');
            $pincode->pincode = $request->input('pincode');
            $pincode->status = $request->input('status');
            $pincode->save();
    
            return response()->json(['success' => true, 'message' => 'Pincode updated successfully']);
        }
    
        return response()->json(['success' => false, 'message' => 'Pincode not found']);
    }
    
    public function store(Request $request)
    { 
        $request->validate([
            'postname' => 'required|string|max:255',
            'pincode' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'status' => 'required',
        ]);

        try {
            Pincode::create([
                'postname' => $request->postname,
                'pincode' => $request->pincode,
                'district_id' => $request->district_id,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'Pincode added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add Pincode.','error'=>$e]);
        }
    }
}
