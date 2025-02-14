<?php

namespace App\Http\Controllers;
use App\Models\FirstCategory;
use App\Models\SecondCategory;
use App\Models\ThirdCategory;
use Illuminate\Http\Request;

class ThirdCategoryController extends Controller
{
    public function thirdCategoryList(Request $request)
    {    
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;

            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $firstCategoryId = $request->first_category;
            $secondCategoryId = $request->second_category_id;
            $columns = [
                'id',
                'third_category_name',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            $query = ThirdCategory::where('status', '!=', '2')
            ->when($secondCategoryId, function ($query) use ($secondCategoryId) {
                // If second category is provided, filter only by that
                return $query->where('second_category_id', $secondCategoryId);
            }, function ($query) use ($firstCategoryId) {
                // Otherwise, include all second categories under the selected first category
                return $query->whereHas('secondCategory', function ($query) use ($firstCategoryId) {
                    $query->where('first_category_id', $firstCategoryId);
                });
            })
            ->orderBy('status', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('third_category_name', 'like', '%'.$searchValue.'%');
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
                       <button type="button" class="btn btn-danger btn-sm" onclick="editThirdCategory(\''.$row->id.'\')" title="edit"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="deleteThirdCategory(\''.$row->id.'\')" title="delete"><i class="fas fa-trash"></i></button>';
                        if($row->status=='1'){$status="Active";}else if($row->status=='0'){$status="Inactive";}
                return [
                    'id' => $row->id,
                    'third_category_name' => $row->third_category_name,
                    'status' => $status,
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
    public function create()
    {
        $firstCategories = FirstCategory::all(); 
        $secondCategories = SecondCategory::all(); 
        return view('admin.third-category.create', compact('secondCategories','firstCategories'));
    }

    public function view($id)
    {
        $category = ThirdCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.third-category.view', compact('category'))->render()
        ]);
    }

    public function delete($id)
    {
        $category = ThirdCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    public function edit($id) {
        $category = ThirdCategory::find($id);
    
        if ($category) {
            return response()->json(['success' => true, 'data' => $category]);
        }
    
        return response()->json(['success' => false, 'message' => 'Category not found']);
    }
    
    public function update(Request $request, $id) {
        $category = ThirdCategory::find($id);
    
        if ($category) {
            $category->third_category_name = $request->input('third_category_name');
            $category->status = $request->input('status');
            $category->save();
    
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        }
    
        return response()->json(['success' => false, 'message' => 'Category not found']);
    }
    
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'third_category_name' => 'required|string|max:255',
            'second_category_id' => 'required|exists:second_categories,id',
            'status' => 'required',
        ]);

        try {
            ThirdCategory::create([
                'third_category_name' => $request->third_category_name,
                'second_category_id' => $request->second_category_id,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'Category added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add category.','error'=>$e]);
        }
    }
}
