<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function __invoke(): View
    {
        $students = Student::query()
            ->with(['lessons' => fn ($query) => $query->latest('session_date')->latest('id')])
            ->orderByDesc('last_note_date')
            ->orderBy('name')
            ->get();

        $studentsWithNotes = $students->filter(function (Student $student): bool {
            return filled($student->last_note)
                || $student->lessons->contains(fn ($lesson) => filled($lesson->notes));
        })->values();

        return view('notes.index', [
            'students' => $students,
            'studentsWithNotes' => $studentsWithNotes,
        ]);
    }
}
