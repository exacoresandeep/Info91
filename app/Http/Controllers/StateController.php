<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function stateList(Request $request)
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
                'state_name',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = State::where('status', '!=', '2')->orderBy('status', 'desc')->orderBy('state_name', 'asc')
                ->orderBy($orderColumn, $orderBy);

            if ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    $query->where('state_name', 'like', '%' . $searchValue . '%');
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

            $formattedData = $data->map(function ($row) {
                $action = '<button type="button" class="btn btn-info btn-sm" onclick="handleAction(\'' . $row->id . '\',\'view\')" title="view"><i class="fas fa-eye"></i></button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="editState(\'' . $row->id . '\')" title="edit"><i class="fas fa-pen"></i></button>
                    <button type="button" class="btn btn-success btn-sm" onclick="deleteState(\'' . $row->id . '\')" title="delete"><i class="fas fa-trash"></i></button>';
                $status = $row->status == '1' ? "Active" : "Inactive";

                return [
                    'id' => $row->id,
                    'state_name' => $row->state_name,
                    'status' => $status,
                    'created_at' => $row->created_at->format('Y-m-d H:i:s'),
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

    public function view($id)
    {
        $state = State::find($id);

        if (!$state) {
            return response()->json(['success' => false, 'message' => 'State not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.state.view', compact('state'))->render()
        ]);
    }

    public function delete($id)
    {
        $state = State::find($id);

        if (!$state) {
            return response()->json(['success' => false, 'message' => 'State not found.'], 404);
        }

        $state->delete();

        return response()->json(['success' => true, 'message' => 'State deleted successfully.']);
    }

    public function edit($id)
    {
        $state = State::find($id);

        if ($state) {
            return response()->json(['success' => true, 'data' => $state]);
        }

        return response()->json(['success' => false, 'message' => 'State not found']);
    }

    public function update(Request $request, $id)
    {
        $state = State::find($id);

        if ($state) {
            $state->state_name = $request->input('state_name');
            $state->status = $request->input('status');
            $state->save();

            return response()->json(['success' => true, 'message' => 'State updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'State not found']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        try {
            State::create([
                'state_name' => $request->state_name,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'State added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add state.']);
        }
    }

    public function list()
    {
        $states = State::select('id', 'state_name as name')->get();

        if ($states->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No states found.']);
        }

        return response()->json(['success' => true, 'data' => $states]);
    }
}
