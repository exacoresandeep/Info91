<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
class PlanController extends Controller
{
    public function planList(Request $request)
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
                'plan_name',
                'amount',
                'duration',
                'tax',
                'total_members',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = Plan::where('status', '!=', '2')->orderBy('status', 'desc')->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            if ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    $query->where('plan_name', 'like', '%' . $searchValue . '%');
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
                    <button type="button" class="btn btn-danger btn-sm" onclick="editPlan(\'' . $row->id . '\')" title="edit"><i class="fas fa-pen"></i></button>
                    <button type="button" class="btn btn-success btn-sm" onclick="deletePlan(\'' . $row->id . '\')" title="delete"><i class="fas fa-trash"></i></button>';
                $status = $row->status == '1' ? "Active" : "Inactive";

                return [
                    'id' => $row->id,
                    'plan_name' => $row->plan_name,
                    'amount' => $row->amount,
                    'duration' => $row->duration,
                    'tax' => $row->tax,
                    'total_members' => $row->total_members,
                    'status' => $status,
                    'created_at' => $row->created_at?$row->created_at->format('Y-m-d H:i:s'):"NA",
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
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.plan.view', compact('plan'))->render()
        ]);
    }

    public function delete($id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan not found.'], 404);
        }

        $plan->delete();

        return response()->json(['success' => true, 'message' => 'Plan deleted successfully.']);
    }

    public function edit($id)
    {
        $plan = Plan::find($id);

        if ($plan) {
            return response()->json(['success' => true, 'data' => $plan]);
        }

        return response()->json(['success' => false, 'message' => 'Plan not found']);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::find($id);

        if ($plan) {
            $plan->plan_name = $request->input('plan_name');
            $plan->amount = $request->input('amount');
            $plan->duration = $request->input('duration');
            $plan->tax = $request->input('tax');
            $plan->total_members = $request->input('total_members');
            $plan->status = $request->input('status');
            $plan->save();

            return response()->json(['success' => true, 'message' => 'Plan updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Plan not found']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        try {
            Plan::create([
                'plan_name' => $request->plan_name,
                'amount' => $request->amount,
                'duration' => $request->duration,
                'tax' => $request->tax,
                'total_members' => $request->total_members,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'Plan added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add plan.']);
        }
    }

    public function list()
    {
        $plans = Plan::select('id', 'plan_name as name')->get();

        if ($plans->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No plans found.']);
        }

        return response()->json(['success' => true, 'data' => $plans]);
    }
}
