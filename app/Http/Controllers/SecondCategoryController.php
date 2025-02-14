<?php

namespace App\Http\Controllers;
use App\Models\FirstCategory;
use App\Models\SecondCategory;
use Illuminate\Http\Request;

class SecondCategoryController extends Controller
{
    public function secondCategoryList(Request $request)
    {    
        if ($request->ajax()) {
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;

            $orderColumnIndex = $request->order[0]['column'] ?? 0;
            $orderBy = $request->order[0]['dir'] ?? 'desc';
            $searchValue = $request->search['value'] ?? '';
            $firstCategoryId = $request->first_category_id;
            $columns = [
                'id',
                'second_category_name',
                'status',
                'created_at'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            $query = SecondCategory::where('status', '!=', '2')
            ->when($firstCategoryId, function ($query) use ($firstCategoryId) {
                return $query->where('first_category_id', $firstCategoryId); // Apply filter
            })->orderBy('status', 'desc')->orderBy('created_at', 'desc')
                ->orderBy($orderColumn, $orderBy);

            // Apply search filter if any search value is provided
            if ($searchValue) {
                $query->where(function($query) use ($searchValue) {
                    $query->where('second_category_name', 'like', '%'.$searchValue.'%');
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
                       <button type="button" class="btn btn-danger btn-sm" onclick="editSecondCategory(\''.$row->id.'\')" title="edit"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-success btn-sm" onclick="deleteSecondCategory(\''.$row->id.'\')" title="delete"><i class="fas fa-trash"></i></button>';
                        if($row->status=='1'){$status="Active";}else if($row->status=='0'){$status="Inactive";}
                return [
                    'id' => $row->id,
                    'second_category_name' => $row->second_category_name,
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
        $firstCategories = FirstCategory::all(); // Retrieve all first categories
        // dd($firstCategories);
        return view('admin.second-category.create', compact('firstCategories'));
    }

    public function view($id)
    {
        $category = SecondCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => view('admin.second-category.view', compact('category'))->render()
        ]);
    }

    public function delete($id)
    {
        $category = SecondCategory::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    public function edit($id) {
        $category = SecondCategory::find($id);
    
        if ($category) {
            return response()->json(['success' => true, 'data' => $category]);
        }
    
        return response()->json(['success' => false, 'message' => 'Category not found']);
    }
    
    public function update(Request $request, $id) {
        $category = SecondCategory::find($id);
    
        if ($category) {
            $category->second_category_name = $request->input('second_category_name');
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
            'second_category_name' => 'required|string|max:255',
            'first_category_id' => 'required|exists:first_categories,id',
            'status' => 'required',
        ]);

        try {
            SecondCategory::create([
                'second_category_name' => $request->second_category_name,
                'first_category_id' => $request->first_category_id,
                'status' => $request->status,
            ]);

            return response()->json(['success' => true, 'message' => 'Category added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add category.','error'=>$e]);
        }
    }

    public function list(Request $request)
    {
        $firstCategoryId = $request->input('first_category_id');
        $categories = SecondCategory::where('first_category_id',$firstCategoryId)
        ->select('id', 'second_category_name as name')->get();
        
        if ($categories->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No categories found.']);
        }
        
        return response()->json(['success' => true, 'data' => $categories]);
    }

}
