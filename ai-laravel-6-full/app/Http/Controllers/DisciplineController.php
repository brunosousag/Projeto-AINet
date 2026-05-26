<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\DisciplineFormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DisciplineController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,App\Models\Discipline', only: ['index']),
            new Middleware('can:create,App\Models\Discipline', only: ['create', 'store']),
            new Middleware('can:view,discipline', only: ['show']),
            new Middleware('can:update,discipline', only: ['edit', 'update']),
            new Middleware('can:delete,discipline', only: ['destroy']),
            new Middleware('can:viewMy,App\Models\Discipline', only: ['myDisciplines']),
        ];
    }

    /**
     * Display a listing of the resource - first version. Search teachers name using joins
     */
    // public function index(Request $request): View
    // {
    //     $filterByCourse = $request->query('course');
    //     $filterByYear = $request->year;
    //     $filterBySemester = $request->input('semester');
    //     $filterByTeacher = $request->teacher;
    //     $disciplinesQuery = Discipline::query();
    //     if ($filterByCourse !== null) {
    //         $disciplinesQuery->where('course', $filterByCourse);
    //     }
    //     if ($filterByYear !== null) {
    //         $disciplinesQuery->where('year', $filterByYear);
    //     }
    //     if ($filterBySemester !== null) {
    //         $disciplinesQuery->where('semester', $filterBySemester);
    //     }
    //     if ($filterByTeacher !== null) {
    //         $disciplinesQuery->join('teachers_disciplines', 'disciplines.id', '=', 'teachers_disciplines.discipline_id')
    //             ->join('teachers', 'teachers_disciplines.teacher_id', '=', 'teachers.id')
    //             ->join('users', 'teachers.user_id', '=', 'users.id')
    //             ->where('users.name', 'like', "%$filterByTeacher%")
    //             ->select('disciplines.*')
    //             ->distinct();
    //     }
    //     $disciplines = $disciplinesQuery->paginate(20);
    //     return view(
    //         'disciplines.index',
    //         compact('disciplines', 'filterByCourse', 'filterByYear', 'filterBySemester', 'filterByTeacher')
    //     );
    // }

    /**
     * Display a listing of the resource - second version. ID filtering sets
     */
    // public function index(Request $request): View
    // {
    //     $filterByCourse = $request->query('course');
    //     $filterByYear = $request->year;
    //     $filterBySemester = $request->input('semester');
    //     $filterByTeacher = $request->teacher;
    //     $disciplinesQuery = Discipline::query();
    //     if ($filterByCourse !== null) {
    //         $disciplinesQuery->where('course', $filterByCourse);
    //     }
    //     if ($filterByYear !== null) {
    //         $disciplinesQuery->where('year', $filterByYear);
    //     }
    //     if ($filterBySemester !== null) {
    //         $disciplinesQuery->where('semester', $filterBySemester);
    //     }
    //     if ($filterByTeacher !== null) {
    //         $usersIds = DB::table('users')
    //             ->where('type', 'T')
    //             ->where('name', 'like', "%$filterByTeacher%")
    //             ->pluck('id')
    //             ->toArray();
    //         $teachersIds = DB::table('teachers')
    //             ->whereIntegerInRaw('user_id', $usersIds)
    //             ->pluck('id')
    //             ->toArray();
    //         $disciplinesIds = DB::table('teachers_disciplines')
    //             ->whereIntegerInRaw('teacher_id', $teachersIds)
    //             ->pluck('discipline_id')
    //             ->toArray();
    //         $disciplinesQuery->whereIntegerInRaw('id', $disciplinesIds);
    //     }
    //     $disciplines = $disciplinesQuery->with('courseRef')->paginate(20)->withQueryString();
    //     return view(
    //         'disciplines.index',
    //         compact('disciplines', 'filterByCourse', 'filterByYear', 'filterBySemester', 'filterByTeacher')
    //     );
    // }

    /**
     * Display a listing of the resource - third version. Using relationships on the query
     */
    public function index(Request $request): View
    {
        $filterByCourse = $request->query('course');
        $filterByYear = $request->year;
        $filterBySemester = $request->input('semester');
        $filterByTeacher = $request->teacher;
        $disciplinesQuery = Discipline::query();
        if ($filterByCourse !== null) {
            $disciplinesQuery->where('course', $filterByCourse);
        }
        if ($filterByYear !== null) {
            $disciplinesQuery->where('year', $filterByYear);
        }
        if ($filterBySemester !== null) {
            $disciplinesQuery->where('semester', $filterBySemester);
        }
        if ($filterByTeacher !== null) {
            $disciplinesQuery->with('teachers.user')->whereHas(
                'teachers.user',
                function ($userQuery) use ($filterByTeacher) {
                    $userQuery->where('name', 'LIKE', '%' . $filterByTeacher . '%');
                }
            );
        }
        $disciplines = $disciplinesQuery
            ->with('courseRef')
            ->orderBy('year')
            ->orderBy('semester')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        return view(
            'disciplines.index',
            compact('disciplines', 'filterByCourse', 'filterByYear', 'filterBySemester', 'filterByTeacher')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $discipline = new Discipline();
        $courses = Course::all();
        return view('disciplines.create')
            ->with('discipline', $discipline)
            ->with('courses', $courses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function  store(DisciplineFormRequest $request): RedirectResponse
    {
        $NewDiscipline = Discipline::create($request->validated());
        $url = route('disciplines.show', ['discipline' => $NewDiscipline]);
        $htmlMessage = "Discipline <a href='$url'><u>{$NewDiscipline->name}</u></a> ({$NewDiscipline->abbreviation}) has been created successfully!";
        return redirect()->route('disciplines.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }


    /**
     * Display the specified resource.
     */
    public function show(Discipline $discipline): View
    {
        $courses = Course::all();
        return view('disciplines.show')
            ->with('discipline', $discipline)
            ->with('courses', $courses);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discipline $discipline): View
    {
        $courses = Course::all();
        return view('disciplines.edit')
            ->with('discipline', $discipline)
            ->with('courses', $courses);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DisciplineFormRequest $request, Discipline $discipline): RedirectResponse
    {
        $discipline->update($request->validated());
        $url = route('disciplines.show', ['discipline' => $discipline]);
        $htmlMessage = "Discipline <a href='$url'><u>{$discipline->name}</u></a> ({$discipline->abbreviation}) has been updated successfully!";
        return redirect()->route('disciplines.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discipline $discipline): RedirectResponse
    {
        try {
            $url = route('disciplines.show', ['discipline' => $discipline]);
            $totalStudents = DB::scalar(
                'select count(*) from students_disciplines where discipline_id = ?',
                [$discipline->id]
            );
            $totalTeachers = DB::scalar(
                'select count(*) from teachers_disciplines where discipline_id = ?',
                [$discipline->id]
            );
            if ($totalStudents == 0 && $totalTeachers == 0) {
                $discipline->delete();
                $alertType = 'success';
                $alertMsg = "Discipline {$discipline->name} ({$discipline->abbreviation}) has been deleted successfully!";
                return redirect()->route('disciplines.index')
                    ->with('alert-type', $alertType)
                    ->with('alert-msg', $alertMsg);
            } else {
                $alertType = 'warning';
                $studentsStr = match (true) {
                    $totalStudents <= 0 => "",
                    $totalStudents == 1 => "there is 1 student enrolled in it",
                    $totalStudents > 1 => "there are $totalStudents students enrolled in it",
                };
                $teachersStr = match (true) {
                    $totalTeachers <= 0 => "",
                    $totalTeachers == 1 => "it already has 1 teacher",
                    $totalTeachers > 1 => "it already has $totalTeachers teachers",
                };
                $justification = $studentsStr && $teachersStr
                    ? "$teachersStr and $studentsStr"
                    : "$teachersStr$studentsStr";
                $alertMsg = "Discipline <a href='$url'><u>{$discipline->name}</u></a> ({$discipline->abbreviation}) cannot be deleted because $justification.";
            }
        } catch (\Exception $error) {
            $alertType = 'danger';
            $alertMsg = "It was not possible to delete the discipline
                            <a href='$url'><u>{$discipline->name}</u></a> ({$discipline->abbreviation})
                            because there was an error with the operation!";
        }
        return redirect()->back()
            ->with('alert-type', $alertType)
            ->with('alert-msg', $alertMsg);
    }

    public function myDisciplines(Request $request): View
    {
        $idDisciplines = [];
        if ($request->user()?->type == 'T') {
            $idDisciplines = $request->user()?->teacher?->disciplines?->pluck('id')?->toArray();
        } elseif ($request->user()?->type == 'S') {
            $idDisciplines = $request->user()?->student?->disciplines?->pluck('id')?->toArray();
        }
        if (empty($idDisciplines)) {
            return view('disciplines.my')->with('disciplines', new Collection);
        }
        $disciplines = Discipline::whereIntegerInRaw('id', $idDisciplines)
            ->with('courseRef')
            ->orderBy('year')
            ->orderBy('semester')
            ->orderBy('name')
            ->get();
        return view('disciplines.my', compact('disciplines'));
    }
}
