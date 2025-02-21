<?php

namespace App\Http\Controllers;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DistrictImport;

class DistrictController extends Controller
{
    public function importDistrict(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'state_id' => 'required|exists:states,id', 
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            
            $file = $request->file('importFile');
            $stateId = $request->input('state_id');
            
            Excel::import(new DistrictImport($stateId), $file);
            
            return response()->json(['message' => 'Districts imported successfully!'], 200);

        } catch (\Exception $e) {
            
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function districtList(Request $request)
    {    
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;

            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $stateId = $request->state_id;
            $columns = [
                'id',
                'district_name',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = District::where('status', '!=', '2')
            ->when($stateId, function ($query) use ($stateId) {
                return $query->where('state_id', $stateId); // Apply filter
            })->orderBy('status', 'desc')->orderBy('district_name', 'asc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('district_name', 'like', '%'.$searchValue.'%');
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
                        <button type="button" class="btn btn-danger btn-sm" onclick="editDistrict(\''.$row->id.'\')" title="edit"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="deleteDistrict(\''.$row->id.'\')" title="delete"><i class="fas fa-trash"></i></button>';
                        if($row->status=='1'){$status="Active";}else if($row->status=='0'){$status="Inactive";}
                return [
                    'id' => $row->id,
                    'district_name' => $row->district_name,
                    'status' => $status,
                    'created_at' => $row->created_at?$row->created_at->format('Y-m-d H:i:s'):'NA',  // Date formatting
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
        return view('admin.district.create', compact('states'));
    }

    public function view($id)
    {
        $district = District::with('state')->find($id);

        if (!$district) {
            return response()->json(['success' => false, 'message' => 'District not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.district.view', compact('district'))->render()
        ]);
    }

    public function delete($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json(['success' => false, 'message' => 'District not found.'], 404);
        }

        $district->delete();

        return response()->json(['success' => true, 'message' => 'District deleted successfully.']);
    }

    public function edit($id) {
        $district = District::find($id);
    
        if ($district) {
            return response()->json(['success' => true, 'data' => $district]);
        }
    
        return response()->json(['success' => false, 'message' => 'District not found']);
    }
    
    public function update(Request $request, $id) {
        $district = District::find($id);
    
        if ($district) {
            $district->district_name = $request->input('district_name');
            $district->status = $request->input('status');
            $district->save();
    
            return response()->json(['success' => true, 'message' => 'District updated successfully']);
        }
    
        return response()->json(['success' => false, 'message' => 'District not found']);
    }
    
    public function store(Request $request)
    { 
        $request->validate([
            'district_name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
            'status' => 'required',
        ]);

        try {
            District::create([
                'district_name' => $request->district_name,
                'state_id' => $request->state_id,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'District added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add district.','error'=>$e]);
        }
    }

    public function list(Request $request)
    {
        $state_id = $request->input('state_id');
        $district = District::where('state_id',$state_id)
        ->select('id', 'district_name as name')->get();
        
        if ($district->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No district found.']);
        }
        
        return response()->json(['success' => true, 'data' => $district]);
    }

}
    