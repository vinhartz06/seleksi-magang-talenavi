<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoRequest;
use App\Models\Todo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Exports\TodosExport;

class TodoController extends Controller
{
    public function store(StoreTodoRequest $request): JsonResponse
    {
        $data = $request->validated();

        // validate lagi supaya nilai default langsung muncul di response json
        $data['time_tracked'] = $data['time_tracked'] ?? 0;
        $data['status'] = $data['status'] ?? 'pending';

        $todo = Todo::create($data);

        return response()->json([
            'message'   => 'Todo created succesfully!',
            'data'      => $todo,
        ], 201);
        // 201 -> HTTP_CREATED
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new TodosExport, 'todos_report.xlsx');
    }
}
