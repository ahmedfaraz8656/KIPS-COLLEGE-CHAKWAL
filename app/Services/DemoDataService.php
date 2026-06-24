<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use App\Models\Program;
use App\Models\Exam;
use App\Models\ExamSubjectMark;
use App\Models\StudentMark;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\Notice;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Module 19: generates a realistic slice of sample data across every
 * module so Ahmed can see the system working end-to-end before real
 * admissions begin. Every record created here is flagged is_demo=true
 * so it can be wiped with a single click without touching real data.
 */
class DemoDataService
{
    protected array $maleNames = ['Ahmad Ali', 'Bilal Khan', 'Hassan Raza', 'Usman Tariq', 'Fahad Iqbal', 'Zeeshan Ahmed', 'Imran Sheikh', 'Kashif Mehmood'];
    protected array $femaleNames = ['Ayesha Bibi', 'Sana Khalid', 'Rabia Naz', 'Mehwish Aslam', 'Hira Yousaf', 'Komal Saeed', 'Farah Nasir', 'Sadia Younas'];
    protected array $fatherNames = ['Muhammad Ali', 'Abdul Rahim', 'Ghulam Hussain', 'Riasat Khan', 'Iqbal Ahmed', 'Nasir Mehmood'];

    public function load(): array
    {
        $teachers = $this->createTeachers();
        $studentsCount = $this->createStudentsPerSection();
        $exams = $this->createExamsWithMarks();
        $this->createAttendance();
        $this->createFees();
        $this->createNotices();

        return [
            'teachers' => count($teachers),
            'students' => $studentsCount,
            'exams' => count($exams),
        ];
    }

    protected function createTeachers(): array
    {
        $created = [];
        $subjects = ['Physics', 'Mathematics', 'English', 'Computer Science', 'Chemistry'];

        foreach (array_slice($this->maleNames, 0, 4) as $i => $name) {
            $created[] = $this->makeTeacher($name, 'male', $subjects[$i % count($subjects)]);
        }
        foreach (array_slice($this->femaleNames, 0, 4) as $i => $name) {
            $created[] = $this->makeTeacher($name, 'female', $subjects[$i % count($subjects)]);
        }

        return $created;
    }

    protected function makeTeacher(string $name, string $gender, string $subjectHint): Teacher
    {
        $email = 'demo.'.strtolower(str_replace(' ', '.', $name)).'@kipscollege.edu.pk';

        $user = User::create([
            'name' => $name, 'email' => $email, 'password' => Hash::make('Demo@123'),
            'whatsapp' => '0300'.rand(1000000, 9999999), 'gender' => $gender,
            'campus' => 'both', 'status' => true, 'is_demo' => true,
        ]);
        $user->assignRole('Teacher');

        return Teacher::create([
            'user_id' => $user->id, 'name' => $name, 'whatsapp' => $user->whatsapp,
            'email' => $email, 'gender' => $gender, 'campus_access' => 'both',
            'date_of_joining' => now()->subYears(rand(1, 5)), 'status' => true, 'is_demo' => true,
        ]);
    }

    protected function createStudentsPerSection(): int
    {
        $count = 0;
        $sections = Section::where('status', true)->get();

        foreach ($sections as $section) {
            $namesPool = $section->campus === 'boys' ? $this->maleNames : $this->femaleNames;
            $howMany = rand(3, 5);

            for ($i = 0; $i < $howMany; $i++) {
                $name = $namesPool[array_rand($namesPool)].' '.chr(65 + $i);
                $roll = Student::generateRollNumber($section->campus, $section->year, $section->program);

                Student::create([
                    'roll_number' => $roll, 'name' => $name,
                    'father_name' => $this->fatherNames[array_rand($this->fatherNames)],
                    'whatsapp' => '0300'.rand(1000000, 9999999),
                    'previous_school' => 'Govt High School, Chakwal',
                    'campus' => $section->campus, 'year' => $section->year,
                    'program_id' => $section->program_id, 'section_id' => $section->id,
                    'enrollment_date' => now()->subMonths(rand(2, 6)),
                    'ninth_board' => 'BISE Rawalpindi', 'ninth_total_marks' => 1100, 'ninth_obtained_marks' => rand(650, 1000),
                    'tenth_board' => 'BISE Rawalpindi', 'tenth_total_marks' => 1100, 'tenth_obtained_marks' => rand(650, 1050),
                    'status' => 'active', 'is_demo' => true,
                ]);
                $count++;
            }
        }

        return $count;
    }

    protected function createExamsWithMarks(): array
    {
        $created = [];
        $programs = Program::all();
        $defaultsByYear = ['first' => 175, 'second' => 170]; // approximate grand totals

        foreach (['Test 1', 'Test 2'] as $idx => $examName) {
            $exam = Exam::create([
                'name' => $examName, 'type' => 'test', 'sequence' => $idx + 1,
                'exam_date' => now()->subWeeks(4 - $idx * 2),
                'campus_scope' => 'both', 'year_scope' => 'both',
                'is_demo' => true,
            ]);

            foreach ($programs as $program) {
                foreach (['first', 'second'] as $year) {
                    $subjects = $program->subjectsForYear($year)->get();
                    foreach ($subjects as $subject) {
                        ExamSubjectMark::create([
                            'exam_id' => $exam->id, 'program_id' => $program->id, 'year' => $year,
                            'subject_id' => $subject->id, 'total_marks' => $subject->pivot->default_marks,
                        ]);
                    }

                    // Attach relevant sections
                    $sections = Section::where('program_id', $program->id)->where('year', $year)->get();
                    foreach ($sections as $section) {
                        $exam->sections()->syncWithoutDetaching([$section->id]);

                        // Generate marks for demo students in this section
                        $students = Student::where('section_id', $section->id)->where('is_demo', true)->get();
                        foreach ($students as $student) {
                            foreach ($subjects as $subject) {
                                $total = $subject->pivot->default_marks;
                                StudentMark::create([
                                    'student_id' => $student->id, 'exam_id' => $exam->id,
                                    'subject_id' => $subject->id, 'section_id' => $section->id,
                                    'total_marks' => $total,
                                    'obtained_marks' => rand((int)($total * 0.5), $total),
                                    'entered_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            $created[] = $exam;
        }

        return $created;
    }

    protected function createAttendance(): void
    {
        $students = Student::where('is_demo', true)->get();

        foreach ($students as $student) {
            for ($d = 30; $d >= 0; $d--) {
                $date = now()->subDays($d);
                if ($date->isSunday()) continue;

                $roll = rand(1, 100);
                $status = $roll <= 85 ? 'present' : ($roll <= 95 ? 'absent' : 'leave');

                Attendance::create([
                    'student_id' => $student->id, 'section_id' => $student->section_id,
                    'date' => $date->toDateString(), 'status' => $status,
                    'is_late' => $status === 'present' && rand(1, 10) === 1,
                    'marked_at_time' => '08:'.rand(10, 50),
                ]);
            }
        }
    }

    protected function createFees(): void
    {
        $category = FeeCategory::first();
        if (!$category) return;

        foreach (Student::where('is_demo', true)->limit(10)->get() as $student) {
            Fee::create([
                'student_id' => $student->id, 'fee_category_id' => $category->id,
                'payment_date' => now()->subDays(rand(1, 20)),
                'amount_due' => 5000, 'amount_paid' => rand(0, 1) ? 5000 : 3000,
                'payment_mode' => 'cash', 'receipt_number' => Fee::generateReceiptNumber(),
                'is_demo' => true,
            ]);
        }
    }

    protected function createNotices(): void
    {
        Notice::create([
            'title' => '[DEMO] Mid-Term Exam Schedule Announced',
            'content' => 'This is sample notice content for demonstration purposes.',
            'target' => 'all', 'campus_scope' => 'both', 'priority' => 'important',
            'post_date' => now(),
        ]);
    }

    /**
     * Deletes ALL demo-flagged records across every table. Real data
     * (is_demo = false) is never touched.
     */
    public function deleteAll(): void
    {
        StudentMark::whereHas('student', fn ($q) => $q->where('is_demo', true))->delete();
        Attendance::whereHas('student', fn ($q) => $q->where('is_demo', true))->delete();
        Fee::where('is_demo', true)->delete();
        Exam::where('is_demo', true)->each(function ($exam) {
            $exam->subjectMarks()->delete();
            $exam->sections()->detach();
            $exam->delete();
        });
        Notice::where('title', 'like', '[DEMO]%')->delete();
        Student::where('is_demo', true)->forceDelete();

        Teacher::where('is_demo', true)->each(function ($teacher) {
            $teacher->user?->delete();
            $teacher->sectionAssignments()->delete();
            $teacher->inchargeOf()->delete();
            $teacher->forceDelete();
        });
    }
}
