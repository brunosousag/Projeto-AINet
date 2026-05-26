<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Discipline;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\CourseFormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CourseController extends Controller implements HasMiddleware
{
    use \App\Traits\CourseImageFileStorage;

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,App\Models\Course', only: ['index']),
            new Middleware('can:create,App\Models\Course', only: ['create', 'store']),
            new Middleware('can:viewShowCase,App\Models\Course', only: ['showCase']),
            new Middleware('can:viewCurriculum,App\Models\Course', only: ['showCurriculum']),
            new Middleware('can:view,course', only: ['show']),
            new Middleware('can:update,course', only: ['edit', 'update']),
            new Middleware('can:delete,course', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $allCourses = Course::orderBy('type')->orderBy('name')->paginate(20);
        //debug($allCourses);
        return view('courses.index')->with('courses', $allCourses);
    }

    public function showCase(): View
    {
        $allCourses = Course::all();
        return view('courses.showcase')->with('courses', $allCourses);
    }

    public function showCurriculum(Course $course): View
    {
        return view('courses.showCurriculum')->with('course', $course);
    }

    public function show(Course $course): View
    {
        return view('courses.show')->with('course', $course);
    }

    public function create(): View
    {
        $newCourse = new Course();
        return view('courses.create')->with('course', $newCourse);
    }

    public function store(CourseFormRequest $request): RedirectResponse
    {
        $newCourse = Course::create($request->validated());
        if ($request->image_file) {
            $this->storeCourseImage($request->image_file, $newCourse);
        }
        $url = route('courses.show', ['course' => $newCourse]);
        $htmlMessage = "Course <a href='$url'><strong>{$newCourse->abbreviation}</strong>
                    - '{$newCourse->name}'</a> has been created successfully!";
        return redirect()->route('courses.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }


    public function edit(Course $course): View
    {
        return view('courses.edit')->with('course', $course);
    }

    public function update(CourseFormRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());
        if ($request->image_file) {
            $this->deleteCourseImage($course);
            $this->storeCourseImage($request->image_file, $course);
        }
        $url = route('courses.show', ['course' => $course]);
        $htmlMessage = "Course <a href='$url'><strong>{$course->abbreviation}</strong> -
                    '{$course->name}'</a> has been updated successfully!";
        return redirect()->route('courses.index')
            ->with('alert-type', 'success')
            ->with('alert-msg', $htmlMessage);
    }

    public function destroy(Course $course): RedirectResponse
    {
        try {
            $url = route('courses.show', ['course' => $course]);
            $totalStudents = DB::scalar(
                'select count(*) from students where course = ?',
                [$course->abbreviation]
            );
            $totalDisciplines = DB::scalar(
                'select count(*) from disciplines where course = ?',
                [$course->abbreviation]
            );
            if ($totalStudents == 0 && $totalDisciplines == 0) {
                $abbreviation = $course->abbreviation;
                $course->delete();
                $this->deleteImageFile($abbreviation);
                $alertType = 'success';
                $alertMsg = "Course {$course->name} ({$course->abbreviation}) has been deleted
                            successfully!";
                return redirect()->route('courses.index')
                    ->with('alert-type', $alertType)
                    ->with('alert-msg', $alertMsg);
            } else {
                $alertType = 'warning';
                $studentsStr = match (true) {
                    $totalStudents <= 0 => "",
                    $totalStudents == 1 => "there is 1 student enrolled in it",
                    $totalStudents > 1 => "there are $totalStudents students enrolled in it",
                };
                $disciplinesStr = match (true) {
                    $totalDisciplines <= 0 => "",
                    $totalDisciplines == 1 => "it already includes 1 discipline",
                    $totalDisciplines > 1 => "it already includes $totalDisciplines disciplines",
                };
                $justification = $studentsStr && $disciplinesStr
                    ? "$disciplinesStr and $studentsStr"
                    : "$disciplinesStr$studentsStr";
                $alertMsg = "Course <a href='$url'><u>{$course->name}</u></a>
                            ({$course->abbreviation}) cannot be deleted because $justification.";
            }
        } catch (\Exception $error) {
            $alertType = 'danger';
            $alertMsg = "It was not possible to delete the course
                        <a href='$url'><u>{$course->name}</u></a> ({$course->abbreviation})
                        because there was an error with the operation!";
        }
        return redirect()->back()
            ->with('alert-type', $alertType)
            ->with('alert-msg', $alertMsg);
    }

    public function destroyImage(Course $course): RedirectResponse
    {
        if ($this->deleteCourseImage($course)) {
            return redirect()->back()
                ->with('alert-type', 'success')
                ->with('alert-msg', "Image of course {$course->abbreviation} has been deleted.");
        } else {
            return redirect()->back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', "Photo of teacher {$course->abbreviation} does not exist.");
        }
    }
}
