<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\StudentScoreService;
use App\Support\Quran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::query()->with('parentContact')->orderBy('name');
        $search = trim((string) $request->get('q', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('parentContact', function ($parent) use ($search) {
                        $parent->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query->get();
        $selected = $request->filled('id')
            ? Student::with(['lessons', 'parentContact'])->find($request->integer('id'))
            : $students->first();

        if ($selected && $selected->relationLoaded('lessons') === false) {
            $selected->load('lessons', 'parentContact');
        }

        $latestAppointment = $selected
            ? $selected->appointments()->orderByDesc('scheduled_date')->orderByDesc('id')->first()
            : null;

        return view('students.index', compact('students', 'selected', 'search', 'latestAppointment'));
    }

    public function create(): View
    {
        return view('students.form', ['student' => new Student([
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 1,
        ])]);
    }

    public function store(StudentRequest $request, StudentScoreService $scores): RedirectResponse
    {
        $student = Student::create($request->studentPayload() + [
            'color' => Quran::nextColor(Student::count()),
        ]);

        if ($request->shouldSyncParentContact()) {
            $student->syncParentContact($request->parentContactAttributes());
        }

        $scores->recalculate($student);

        return redirect()
            ->route('students.index', ['id' => $student->id])
            ->with('success', 'تم حفظ ملف الطالب');
    }

    public function edit(Student $student): View
    {
        $student->load('parentContact');

        return view('students.form', compact('student'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->studentPayload());

        if ($request->shouldSyncParentContact()) {
            $student->syncParentContact($request->parentContactAttributes());
        }

        return redirect()
            ->route('students.index', ['id' => $student->id])
            ->with('success', 'تم تحديث ملف الطالب');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'تم حذف الطالب');
    }
}
