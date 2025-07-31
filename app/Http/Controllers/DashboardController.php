<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Modules\Schools\App\Models\School;
use App\Models\User;
use Modules\Admissions\App\Models\Student;
use Modules\Teachers\Models\Teacher;
use Modules\ClassesSections\App\Models\ClassModel;
use Modules\ClassesSections\App\Models\Section;
use Modules\Fees\App\Models\Fee;
use Modules\PapersQuestions\App\Models\Paper;
use Modules\ResultsPromotions\App\Models\ExamResult;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $activeSchoolId = session('active_school_id');

        // Initialize basic data
        $dashboardData = [
            'user' => $user,
            'userRoles' => $user->roles->pluck('name'),
            'activeSchoolId' => $activeSchoolId,
            'schools' => [],
            'stats' => [],
            'recentData' => [],
            'errors' => []
        ];

        try {
            // Handle Super Admin (no school required)
            if ($user->hasRole('superadmin')) {
                $dashboardData = $this->getSuperAdminData($dashboardData);
            }
            // Handle other roles (school required)
            elseif ($user->hasRole(['admin', 'principal', 'teacher'])) {
                if (!$activeSchoolId) {
                    $dashboardData['errors'][] = 'No active school selected. Please select a school.';
                    return Inertia::render('Dashboard', $dashboardData);
                }

                $school = School::find($activeSchoolId);
                if (!$school) {
                    $dashboardData['errors'][] = 'Selected school not found.';
                    return Inertia::render('Dashboard', $dashboardData);
                }

                $dashboardData['activeSchool'] = $school;
                $dashboardData = $this->getRoleBasedData($dashboardData, $user, $school);
            } else {
                $dashboardData['errors'][] = 'No valid role assigned. Please contact administrator.';
            }
        } catch (\Exception $e) {
            $dashboardData['errors'][] = 'An error occurred while loading dashboard data: ' . $e->getMessage();
        }

        return Inertia::render('Dashboard', $dashboardData);
    }

    private function getSuperAdminData($dashboardData)
    {
        // Get all schools for super admin
        $schools = School::all();
        $dashboardData['schools'] = $schools;

        // Get real counts
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassModel::count();


        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $teachersThisMonth = Teacher::whereMonth('created_at', $currentMonth)->count();
        $teachersLastMonth = Teacher::whereMonth('created_at', $lastMonth)->count();
        $teacherChange = $teachersLastMonth > 0 ? round((($teachersThisMonth - $teachersLastMonth) / $teachersLastMonth) * 100, 1) : 0;

        // System-wide statistics with real data
        $dashboardData['stats'] = [
            [
                'label' => 'Total Schools',
                'value' => $schools->count(),
                'icon' => 'school',
                'color' => 'blue',
                'change' => $schools->count() > 0 ? 'Active schools' : 'No schools yet'
            ],
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'green',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'purple',
                'change' => $teacherChange > 0 ? "+{$teacherChange}% this month" : "{$teacherChange}% this month"
            ],
            [
                'label' => 'Total Classes',
                'value' => $totalClasses,
                'icon' => 'graduation-cap',
                'color' => 'orange',
                'change' => $totalClasses > 0 ? 'Active classes' : 'No classes yet'
            ]
        ];

        // Add recent data
        $dashboardData['recentData'] = [
            'recentStudents' => Student::with('school')->latest()->take(5)->get(),
            'recentFees' => Fee::with(['student', 'school'])->latest()->take(5)->get(),
            'recentResults' => ExamResult::with(['student', 'school'])->latest()->take(5)->get(),
        ];

        return $dashboardData;
    }

    private function getRoleBasedData($dashboardData, $user, $school)
    {
        if ($user->hasRole('admin')) {
            $dashboardData = $this->getAdminData($dashboardData, $school);
        } elseif ($user->hasRole('principal')) {
            $dashboardData = $this->getPrincipalData($dashboardData, $school);
        } elseif ($user->hasRole('teacher')) {
            $dashboardData = $this->getTeacherData($dashboardData, $school, $user);
        }

        return $dashboardData;
    }

    private function getAdminData($dashboardData, $school)
    {
        $schoolId = $school->id;

        // Get real counts for this school
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalClasses = ClassModel::where('school_id', $schoolId)->count();
        $totalSections = Section::whereHas('classes', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->count();

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $teachersThisMonth = Teacher::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $teachersLastMonth = Teacher::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $teacherChange = $teachersLastMonth > 0 ? round((($teachersThisMonth - $teachersLastMonth) / $teachersLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'blue',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'green',
                'change' => $teacherChange > 0 ? "+{$teacherChange}% this month" : "{$teacherChange}% this month"
            ],
            [
                'label' => 'Total Classes',
                'value' => $totalClasses,
                'icon' => 'graduation-cap',
                'color' => 'purple',
                'change' => $totalClasses > 0 ? 'Active classes' : 'No classes yet'
            ],
            [
                'label' => 'Total Sections',
                'value' => $totalSections,
                'icon' => 'layer-group',
                'color' => 'orange',
                'change' => $totalSections > 0 ? 'Active sections' : 'No sections yet'
            ]
        ];

        // Add recent data for this school
        $dashboardData['recentData'] = [
            'recentStudents' => Student::where('school_id', $schoolId)->with('school')->latest()->take(5)->get(),
            'recentFees' => Fee::where('school_id', $schoolId)->with(['student', 'school'])->latest()->take(5)->get(),
            'pendingFees' => Fee::where('school_id', $schoolId)->where('status', 'unpaid')->count(),
            'overdueFees' => Fee::where('school_id', $schoolId)->where('due_date', '<', now())->where('status', 'unpaid')->count(),
        ];

        return $dashboardData;
    }

    private function getPrincipalData($dashboardData, $school)
    {
        $schoolId = $school->id;

        // Get real counts for this school
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Teacher::where('school_id', $schoolId)->count();
        $totalResults = ExamResult::where('school_id', $schoolId)->count();
        $totalPapers = Paper::where('school_id', $schoolId)->count();

        // Calculate performance metrics
        $averagePerformance = ExamResult::where('school_id', $schoolId)->avg('percentage') ?? 0;
        $averagePerformance = round($averagePerformance, 1);

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $studentsThisMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $currentMonth)->count();
        $studentsLastMonth = Student::where('school_id', $schoolId)->whereMonth('created_at', $lastMonth)->count();
        $studentChange = $studentsLastMonth > 0 ? round((($studentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'Total Students',
                'value' => $totalStudents,
                'icon' => 'users',
                'color' => 'blue',
                'change' => $studentChange > 0 ? "+{$studentChange}% this month" : "{$studentChange}% this month"
            ],
            [
                'label' => 'Total Teachers',
                'value' => $totalTeachers,
                'icon' => 'user-tie',
                'color' => 'green',
                'change' => $totalTeachers > 0 ? 'Active teachers' : 'No teachers yet'
            ],
            [
                'label' => 'Average Performance',
                'value' => $averagePerformance . '%',
                'icon' => 'chart-line',
                'color' => 'purple',
                'change' => $averagePerformance > 0 ? 'Based on all results' : 'No results yet'
            ],
            [
                'label' => 'Total Papers',
                'value' => $totalPapers,
                'icon' => 'trophy',
                'color' => 'orange',
                'change' => $totalPapers > 0 ? 'Created papers' : 'No papers yet'
            ]
        ];

        // Add recent data for this school
        $dashboardData['recentData'] = [
            'recentResults' => ExamResult::where('school_id', $schoolId)->with(['student', 'school'])->latest()->take(5)->get(),
            'topPerformers' => ExamResult::where('school_id', $schoolId)->orderBy('percentage', 'desc')->with(['student', 'school'])->take(5)->get(),
            'recentPapers' => Paper::where('school_id', $schoolId)->latest()->take(5)->get(),
        ];

        return $dashboardData;
    }

    private function getTeacherData($dashboardData, $school, $user)
    {
        $schoolId = $school->id;

        // Find teacher record for this user and school
        $teacher = Teacher::where('user_id', $user->id)->where('school_id', $schoolId)->first();

        if (!$teacher) {
            $dashboardData['errors'][] = 'Teacher profile not found for this school.';
            return $dashboardData;
        }

        // Get real counts for this teacher
        $myStudents = Student::where('school_id', $schoolId)->where('class_teacher_id', $teacher->id)->count();
        $myClasses = ClassModel::where('school_id', $schoolId)->where('teacher_id', $teacher->id)->count();
        $papersCreated = Paper::where('school_id', $schoolId)->where('created_by', $user->id)->count();
        $resultsPublished = ExamResult::where('school_id', $schoolId)->where('teacher_id', $teacher->id)->count();

        // Calculate monthly changes
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $papersThisMonth = Paper::where('school_id', $schoolId)->where('created_by', $user->id)->whereMonth('created_at', $currentMonth)->count();
        $papersLastMonth = Paper::where('school_id', $schoolId)->where('created_by', $user->id)->whereMonth('created_at', $lastMonth)->count();
        $paperChange = $papersLastMonth > 0 ? round((($papersThisMonth - $papersLastMonth) / $papersLastMonth) * 100, 1) : 0;

        $dashboardData['stats'] = [
            [
                'label' => 'My Students',
                'value' => $myStudents,
                'icon' => 'users',
                'color' => 'blue',
                'change' => $myStudents > 0 ? 'Current students' : 'No students assigned'
            ],
            [
                'label' => 'My Classes',
                'value' => $myClasses,
                'icon' => 'graduation-cap',
                'color' => 'green',
                'change' => $myClasses > 0 ? 'Active classes' : 'No classes assigned'
            ],
            [
                'label' => 'Papers Created',
                'value' => $papersCreated,
                'icon' => 'file-alt',
                'color' => 'purple',
                'change' => $paperChange > 0 ? "+{$paperChange}% this month" : "{$paperChange}% this month"
            ],
            [
                'label' => 'Results Published',
                'value' => $resultsPublished,
                'icon' => 'chart-bar',
                'color' => 'orange',
                'change' => $resultsPublished > 0 ? 'Published results' : 'No results yet'
            ]
        ];

        // Add recent data for this teacher
        $dashboardData['recentData'] = [
            'myStudents' => Student::where('school_id', $schoolId)->where('class_teacher_id', $teacher->id)->take(5)->get(),
            'myPapers' => Paper::where('school_id', $schoolId)->where('created_by', $user->id)->latest()->take(5)->get(),
            'myResults' => ExamResult::where('school_id', $schoolId)->where('teacher_id', $teacher->id)->latest()->take(5)->get(),
        ];

        return $dashboardData;
    }
}
