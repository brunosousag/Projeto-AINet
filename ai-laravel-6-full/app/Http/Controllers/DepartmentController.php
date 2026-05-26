<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\DepartmentFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,App\Models\Department', only: ['index']),
            new Middleware('can:create,App\Models\Department', only: ['create', 'store']),
            new Middleware('can:view,department', only: ['show']),
            new Middleware('can:update,department', only: ['edit', 'update']),
            new Middleware('can:delete,department', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('departments.index')
            ->with('departments', Department::orderBy('name')->paginate(20));
    }

    public function create(): View
    {
        $newDepartment = new Department();
        return view('departments.create')
            ->with('department', $newDepartment);
    }

    public function store(DepartmentFormRequest $request): RedirectResponse
    {
        $newDepartment = Department::create($request->validated());
        $url = route('departments.show', ['department' => $newDepartment]);
        $htmlMessage = "Department <a href='$url'><u>{$newDepartment->name}</u></a> ({$newDepartment->abbreviation}) has been created successfully!";
        return redirect()->route('departments.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }

    public function edit(Department $department): View
    {
        return view('departments.edit')
            ->with('department', $department);
    }

    public function update(DepartmentFormRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());
        $url = route('departments.show', ['department' => $department]);
        $htmlMessage = "Department <a href='$url'><u>{$department->name}</u></a> ({$department->abbreviation}) has been updated successfully!";
        return redirect()->route('departments.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }

    public function destroy(Department $department): RedirectResponse
    {
        try {
            $url = route('departments.show', ['department' => $department]);
            $totalTeachers = DB::scalar(
                'select count(*) from teachers where department = ?',
                [$department->abbreviation]
            );
            if ($totalTeachers == 0) {
                $department->delete();
                $alertType = 'success';
                $alertMsg = "Department {$department->name} ({$department->abbreviation}) has been deleted successfully!";
                return redirect()->route('departments.index')
                    ->with('alert-type', $alertType)
                    ->with('alert-msg', $alertMsg);
            } else {
                $alertType = 'warning';
                $justification = match (true) {
                    $totalTeachers <= 0 => "",
                    $totalTeachers == 1 => "there is 1 teacher in the department",
                    $totalTeachers > 1 => "there are $totalTeachers teachers in the department",
                };
                $alertMsg = "Department <a href='$url'><u>{$department->name}</u></a> ({$department->abbreviation}) cannot be deleted because $justification.";
            }
        } catch (\Exception $error) {
            $alertType = 'danger';
            $alertMsg = "It was not possible to delete the department
                            <a href='$url'><u>{$department->name}</u></a> ({$department->abbreviation})
                            because there was an error with the operation!";
        }
        return redirect()->back()
            ->with('alert-type', $alertType)
            ->with('alert-msg', $alertMsg);
    }

    public function show(Department $department): View
    {
        return view('departments.show')->with('department', $department);
    }
}
