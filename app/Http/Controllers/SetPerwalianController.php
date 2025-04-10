<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use App\Services\StudentSyncService;
use App\Models\Absensi;
use App\Models\BeritaAcara;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF; // Make sure to install barryvdh/laravel-dompdf and set alias in config/app.php

class SetPerwalianController extends Controller
{
    protected $studentSyncService;

    // Existing methods...
    // -------------------------------------------

    /**
     * Display the History page.
     *
     * @return \Illuminate\View\View
     */
    public function __construct(StudentSyncService $studentSyncService)
    {
        $this->studentSyncService = $studentSyncService;
    }

    public function histori(Request $request)
    {
        // 1. Identify the logged-in user
        $username = session('user')['username'] ?? null;
        if (!$username) {
            Log::warning('No username found in session for histori page', ['session' => session()->all()]);
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Dosen::where('username', $username)->first();
        if (!$user) {
            Log::error('No Dosen found for username in histori page', ['username' => $username]);
            return redirect()->route('login')->with('error', 'User not found or not authorized.');
        }

        // 2. Retrieve all completed Perwalian for this dosen
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->where('Status', 'Completed')
            ->get();

        // 3. Filter by search text (if any)
        $searchTerm = $request->input('search');
        if ($searchTerm) {
            $lowerSearch = mb_strtolower($searchTerm);
            $perwalianRecords = $perwalianRecords->filter(function ($item) use ($lowerSearch) {
                // Match search against the 'kelas', 'nama', or the raw date string
                $kelas = mb_strtolower($item->kelas);
                $nama = mb_strtolower($item->nama);
                $tanggal = $item->Tanggal;
                return (str_contains($kelas, $lowerSearch) ||
                        str_contains($nama, $lowerSearch) ||
                        str_contains($tanggal, $lowerSearch));
            })->values();
        }

        // 4. Categorize each date into Semester Baru, Sebelum UTS, or Sebelum UAS
        //    based on new date rules:
        //    Semester Genap:
        //      - Semester Baru: 1 Januari – 1 Februari
        //      - Sebelum UTS: 2 Februari – 10 Maret
        //      - Sebelum UAS: 11 Maret – 19 Mei
        //    Semester Ganjil:
        //      - Semester Baru: 1 Agustus – 1 September
        //      - Sebelum UTS: 2 September – 14 Oktober
        //      - Sebelum UAS: 15 Oktober – 11 Desember
        $semesterBaru = [];
        $sebelumUts   = [];
        $sebelumUas   = [];

        foreach ($perwalianRecords as $record) {
            $dateObj = Carbon::parse($record->Tanggal);
            $month   = $dateObj->month;
            $day     = $dateObj->day;
            $category = $this->determineCategory($month, $day);
            if ($category === 'semester_baru') {
                $semesterBaru[] = $record;
            } elseif ($category === 'sebelum_uts') {
                $sebelumUts[] = $record;
            } elseif ($category === 'sebelum_uas') {
                $sebelumUas[] = $record;
            } else {
                $semesterBaru[] = $record;
            }
        }

        // 5. Filter by category (if selected)
        $selectedCategory = $request->input('category');
        if ($selectedCategory) {
            if ($selectedCategory === 'semester_baru') {
                $sebelumUts = [];
                $sebelumUas = [];
            } elseif ($selectedCategory === 'sebelum_uts') {
                $semesterBaru = [];
                $sebelumUas   = [];
            } elseif ($selectedCategory === 'sebelum_uas') {
                $semesterBaru = [];
                $sebelumUts   = [];
            }
        }

        // 6. Return the Blade view with the grouped data
        return view('dosen.histori', [
            'semesterBaru' => $semesterBaru,
            'sebelumUts'   => $sebelumUts,
            'sebelumUas'   => $sebelumUas,
        ]);
    }

    /**
     * Determine which category a given date (month/day) belongs to
     * based on the new rules.
     */
    private function determineCategory($month, $day)
    {
        // Semester Genap: (months 1 to 5)
        if ($month == 1) {
            return 'semester_baru';
        }
        if ($month == 2) {
            if ($day == 1) {
                return 'semester_baru';
            }
            if ($day >= 2 && $day <= 29) {
                return 'sebelum_uts';
            }
        }
        if ($month == 3) {
            if ($day <= 10) {
                return 'sebelum_uts';
            }
            if ($day >= 11 && $day <= 31) {
                return 'sebelum_uas';
            }
        }
        if ($month == 4 || $month == 5) {
            if ($month == 5 && $day > 19) {
                return 'semester_baru';
            }
            return 'sebelum_uas';
        }
        // Semester Ganjil: (months 8 to 12)
        if ($month == 8) {
            return 'semester_baru';
        }
        if ($month == 9) {
            if ($day == 1) {
                return 'semester_baru';
            }
            if ($day >= 2 && $day <= 30) {
                return 'sebelum_uts';
            }
        }
        if ($month == 10) {
            if ($day <= 14) {
                return 'sebelum_uts';
            }
            if ($day >= 15 && $day <= 31) {
                return 'sebelum_uas';
            }
        }
        if ($month == 11) {
            return 'sebelum_uas';
        }
        if ($month == 12) {
            if ($day <= 11) {
                return 'sebelum_uas';
            }
        }
        return 'semester_baru';
    }

    // ... (Other existing methods: index, getCalendar, store, destroy, etc.)

    public function index(Request $request)
{
    $username = session('user')['username'] ?? null;
    Log::info('Attempting to access setPerwalian index', ['username' => $username]);

    if (!$username) {
        Log::warning('No username found in session', ['session' => session()->all()]);
        return redirect()->route('login')->with('error', 'Please log in to access this page.');
    }

    $user = Dosen::where('username', $username)->first();
    if (!$user) {
        Log::error('No Dosen found for username', ['username' => $username]);
        return redirect()->route('login')->with('error', 'User not found or not authorized.');
    }

    // Retrieve notifications for the dosen using Laravel's built-in notification system
    $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

    $classes = [];
    $dosenRecord = DB::table('dosen_wali')
        ->where('username', $username)
        ->first();
    if ($dosenRecord && !empty($dosenRecord->kelas)) {
        $classes = array_map('trim', explode(',', $dosenRecord->kelas));
    } else {
        Log::warning('No classes found for dosen', ['username' => $username]);
    }
    $defaultClass = count($classes) === 1 ? $classes[0] : null;

    $scheduledDatesByClass = [];
    $scheduledClasses = [];
    if ($user) {
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->where('Status', 'Scheduled')
            ->get(['kelas', 'Tanggal']);
        $scheduledClasses = $perwalianRecords->pluck('kelas')->toArray();
        foreach ($perwalianRecords as $record) {
            $date = Carbon::parse($record->Tanggal)->format('Y-m-d');
            $scheduledDatesByClass[$record->kelas][] = $date;
        }
    }

    $month = $request->query('month', now()->format('Y-m'));
    $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    if ($currentDate->lt(Carbon::create(2025, 1, 1))) {
        $currentDate = Carbon::create(2025, 1, 1);
    }
    if ($currentDate->gt(Carbon::create(2027, 12, 1))) {
        $currentDate = Carbon::create(2027, 12, 1);
    }
    $calendarData = $this->prepareCalendarData($currentDate);

    // Example: If you want to use notifications for something else,
    // you can log their count or pass them to the view.
    Log::info('Notifications fetched for dosen', ['count' => $notifications->count()]);

    $apiToken = env('API_TOKEN');
    if (!$apiToken) {
        Log::warning('API_TOKEN not set in .env');
    }
    $dosenResponse = Http::withToken($apiToken)
        ->withOptions(['verify' => false])
        ->asForm()
        ->get('https://cis-dev.del.ac.id/api/library-api/dosen');
    if (!$dosenResponse->successful()) {
        Log::error('Failed to fetch dosen data from API', [
            'status' => $dosenResponse->status(),
            'body' => $dosenResponse->body()
        ]);
    }
    $dosenData = $dosenResponse->json();
    $dosenNotifications = collect();
    if ($dosenData && $notifications->isNotEmpty()) {
        $dosenWaliIds = $notifications->pluck('data.ID_Dosen_Wali')->unique()->filter();
        $dosenNotifications = collect($dosenData)->whereIn('nip', $dosenWaliIds)->values();
    }

    return view('perwalian.setPerwalian', [
        'scheduledClasses' => $scheduledClasses,
        'scheduledDatesByClass' => $scheduledDatesByClass,
        'dosenNotifications' => $dosenNotifications,
        'currentDate' => $currentDate,
        'classes' => $classes,
        'defaultClass' => $defaultClass,
        'calendarData' => $calendarData,
        'notifications' => $notifications, // pass notifications to view if needed
    ]);
}


    public function getCalendar(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        if ($currentDate->lt(Carbon::create(2025, 1, 1))) {
            $currentDate = Carbon::create(2025, 1, 1);
        }
        if ($currentDate->gt(Carbon::create(2027, 12, 1))) {
            $currentDate = Carbon::create(2027, 12, 1);
        }
        $calendarData = $this->prepareCalendarData($currentDate);
        $calendarHtml = view('perwalian.partials.calendar', [
            'currentDate' => $currentDate,
            'calendarData' => $calendarData,
        ])->render();
        return response()->json([
            'calendarHtml' => $calendarHtml,
            'monthLabel' => $currentDate->format('F Y'),
            'prevMonth' => $currentDate->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentDate->copy()->addMonth()->format('Y-m'),
        ]);
    }

    private function prepareCalendarData($currentDate)
    {
        $firstDay = $currentDate->copy()->startOfMonth();
        $lastDay = $currentDate->copy()->endOfMonth();
        $daysInMonth = $lastDay->day;
        $startingDay = $firstDay->dayOfWeek;
        $markedDates = [
            '2025-01-13' => true, '2025-01-14' => true, '2025-01-15' => true, '2025-01-16' => true,
            '2025-01-17' => true, '2025-01-18' => true, '2025-01-21' => true, '2025-01-24' => true,
            '2025-01-25' => true, '2025-02-09' => true, '2025-02-10' => true, '2025-02-11' => true,
            '2025-02-13' => true,
        ];
        $calendarDays = array_fill(0, 42, '');
        $calendarData = [];
        $date = 1;
        for ($i = $startingDay; $i < $startingDay + $daysInMonth; $i++) {
            $calendarDays[$i] = $date;
            $date++;
        }
        for ($i = 0; $i < 42; $i++) {
            $currentDateStr = $calendarDays[$i]
                ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d')
                : '';
            $calendarData[$i] = [
                'day' => $calendarDays[$i],
                'dateStr' => $currentDateStr,
                'isToday' => $currentDateStr === now()->format('Y-m-d'),
                'isMarked' => $currentDateStr && isset($markedDates[$currentDateStr]),
                'isWeekend' => ($i % 7 === 0 || $i % 7 === 6),
                'isPast' => $currentDateStr && Carbon::parse($currentDateStr)->isBefore(now()->startOfDay()),
            ];
        }
        return $calendarData;
    }

    public function store(Request $request)
    {
        Log::info('Store request received', [
            'request' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Validate CSRF, etc.
        if (!$request->hasHeader('X-CSRF-TOKEN') || $request->session()->token() !== $request->header('X-CSRF-TOKEN')) {
            Log::error('CSRF token mismatch in store', [
                'session_token' => $request->session()->token(),
                'request_token' => $request->header('X-CSRF-TOKEN'),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid CSRF token'], 419);
        }

        $validatedData = $request->validate([
            'selectedDate' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:1990-02-01',
                'before_or_equal:2040-06-30',
                function ($attribute, $value, $fail) {
                    $selectedDate = Carbon::parse($value);
                    if ($selectedDate->isBefore(now()->startOfDay())) {
                        $fail('The selected date cannot be in the past.');
                    }
                },
            ],
            'selectedClass' => 'required|string',
        ]);

        try {
            $user = session('user');
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to set a perwalian date.'
                ], 401);
            }

            // Check for an existing scheduled Perwalian for the same class and date
            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user['nip'])
                ->where('Status', 'Scheduled')
                ->where('kelas', $validatedData['selectedClass'])
                ->where('Tanggal', Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'))
                ->first();
            if ($existingPerwalian) {
                return response()->json([
                    'success' => false,
                    'message' => 'A perwalian session is already scheduled for this class on this date. Use the Edit option to delete and request again.'
                ], 400);
            }

            $date = Carbon::parse($validatedData['selectedDate']);
            $year = $date->year;
            $syncYear = $year - 5; // Adjust based on your logic

            // Create the new Perwalian record
            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user['nip'],
                'Tanggal'       => $date->format('Y-m-d'),
                'Status'        => 'Scheduled',
                'nama'          => $user['nama'],
                'kelas'         => $validatedData['selectedClass'],
                'angkatan'      => $year,
            ]);
            if (!$perwalian) {
                Log::error('Failed to create Perwalian record');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Perwalian record.'
                ], 500);
            }
            Log::info('Perwalian created:', [
                'perwalian' => $perwalian->toArray(),
                'ID_Perwalian' => $perwalian->ID_Perwalian,
            ]);

            // Fetch students from your sync service and update their ID_Perwalian
            $students = $this->studentSyncService->fetchStudents($user['pegawai_id'], $syncYear, 2, $validatedData['selectedClass']);
            Log::info('Students fetched for Perwalian', [
                'class' => $validatedData['selectedClass'],
                'student_count' => count($students),
            ]);

            // Map API data to needed fields and sync/update Mahasiswa records
            foreach ($students as $studentData) {
                $nim = $studentData['nim'];
                $username = 'ifs' . substr($nim, 3);
                Log::info('Processing student for Perwalian', [
                    'nim' => $nim,
                    'username' => $username,
                    'class' => $validatedData['selectedClass'],
                    'ID_Perwalian' => $perwalian->ID_Perwalian,
                ]);

                $mahasiswa = Mahasiswa::firstOrCreate(
                    ['nim' => $nim],
                    [
                        'username' => $username,
                        'nama' => $studentData['nama'],
                        'kelas' => $validatedData['selectedClass'],
                        'ID_Dosen' => $user['pegawai_id'],
                        'ID_Perwalian' => $perwalian->ID_Perwalian,
                    ]
                );

                $mahasiswa->ID_Perwalian = $perwalian->ID_Perwalian;
                $mahasiswa->save();

                Log::info('Mahasiswa record updated for perwalian', [
                    'nim' => $mahasiswa->nim,
                    'ID_Perwalian' => $mahasiswa->ID_Perwalian,
                ]);
            }

            // ----- New: Send a universal notification to all affected Mahasiswa -----
            $affectedStudents = Mahasiswa::where('kelas', $validatedData['selectedClass'])
                ->where('ID_Perwalian', $perwalian->ID_Perwalian)
                ->get();
            foreach ($affectedStudents as $student) {
                $student->notify(new UniversalNotification(
                    "Perwalian scheduled for " . $validatedData['selectedDate'] . " (Class: " . $validatedData['selectedClass'] . ")",
                    [
                        'id_perwalian' => $perwalian->ID_Perwalian,
                        'category' => 'perwalian',
                        'action' => 'store'
                    ]
                ));
            }
            // ------------------------------------------------------------------------

            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] .
                ' by dosen NIP: ' . $user['nip'] .
                ' for class: ' . $validatedData['selectedClass']);

            // Fetch updated scheduled dates for response
            $scheduledDatesByClass = [];
            $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user['nip'])
                ->where('Status', 'Scheduled')
                ->get(['kelas', 'Tanggal']);
            foreach ($perwalianRecords as $record) {
                $date = Carbon::parse($record->Tanggal)->format('Y-m-d');
                $scheduledDatesByClass[$record->kelas][] = $date;
            }
            return response()->json([
                'success' => true,
                'message' => 'Perwalian date set successfully for ' . $validatedData['selectedDate'] .
                    ' (Class: ' . $validatedData['selectedClass'] . ')',
                'scheduledDatesByClass' => $scheduledDatesByClass,
                'scheduledClasses' => array_keys($scheduledDatesByClass),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set Perwalian date: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to set Perwalian date: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an existing Perwalian record, clear ID_Perwalian in Mahasiswa,
     * and send a universal notification regarding the deletion.
     */
    public function destroy(Request $request)
    {
        Log::info('Destroy request received', [
            'request' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        if (!$request->hasHeader('X-CSRF-TOKEN') || $request->session()->token() !== $request->header('X-CSRF-TOKEN')) {
            Log::error('CSRF token mismatch in destroy', [
                'session_token' => $request->session()->token(),
                'request_token' => $request->header('X-CSRF-TOKEN'),
                'request_headers' => $request->headers->all()
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid CSRF token'], 419);
        }
        try {
            $username = session('user')['username'] ?? null;
            $user = Dosen::where('username', $username)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to delete a perwalian.'
                ], 401);
            }
            $selectedClass = $request->input('selectedClass');
            if (!$selectedClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class selected for deletion.'
                ], 400);
            }
            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $selectedClass)
                ->first();
            if (!$existingPerwalian) {
                return response()->json([
                    'success' => false,
                    'message' => 'No scheduled perwalian found for this class.'
                ], 404);
            }

            // Retrieve the affected mahasiswa before clearing the perwalian record
            $affectedStudents = Mahasiswa::where('kelas', $selectedClass)
                ->where('ID_Perwalian', $existingPerwalian->ID_Perwalian)
                ->get();

            // Clear ID_Perwalian for affected students
            $updatedCount = Mahasiswa::where('kelas', $selectedClass)
                ->where('ID_Perwalian', $existingPerwalian->ID_Perwalian)
                ->update(['ID_Perwalian' => null]);

            Log::info('Cleared ID_Perwalian for students in class:', [
                'class' => $selectedClass,
                'perwalian_id' => $existingPerwalian->ID_Perwalian,
                'updated_count' => $updatedCount,
            ]);

            // Send a universal notification to each affected student about the deletion
            foreach ($affectedStudents as $student) {
                $student->notify(new UniversalNotification(
                    "Perwalian session for class $selectedClass has been deleted.",
                    [
                        'id_perwalian' => $existingPerwalian->ID_Perwalian,
                        'category' => 'perwalian',
                        'action' => 'destroy'
                    ]
                ));
            }

            // Delete any old notifications that might be stored via legacy method if needed,
            // otherwise, the built-in notifications will remain in the notifications table.
            // (No deletion is required when using Laravel's notification system.)

            // Finally, delete the Perwalian record
            $existingPerwalian->delete();
            Log::info('Perwalian deleted for dosen NIP: ' . $user->nip . ' for class: ' . $selectedClass);

            $scheduledDatesByClass = [];
            $scheduledClasses = [];
            $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->get(['kelas', 'Tanggal']);
            foreach ($perwalianRecords as $record) {
                $date = Carbon::parse($record->Tanggal)->format('Y-m-d');
                $scheduledDatesByClass[$record->kelas][] = $date;
                $scheduledClasses[] = $record->kelas;
            }
            return response()->json([
                'success' => true,
                'message' => 'Perwalian request deleted for class ' . $selectedClass . '. You can now create a new request.',
                'scheduledDatesByClass' => $scheduledDatesByClass,
                'scheduledClasses' => array_unique($scheduledClasses),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete Perwalian: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Perwalian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed histori of a completed Perwalian.
     */
    public function detailedHistori($id)
    {
        // 1. Find the Perwalian record by ID
        $perwalian = Perwalian::find($id);
        if (!$perwalian) {
            return redirect()->route('dosen.histori')->with('error', 'Perwalian not found.');
        }
        // 2. Get all students for this Perwalian
        $mahasiswaRecords = DB::table('mahasiswa')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->orderBy('nama')
            ->get();
        // 3. Fetch Absensi records for these students
        $absensiRecords = DB::table('absensi')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->get()
            ->keyBy('nim');
        // Build students array with status
        $students = [];
        foreach ($mahasiswaRecords as $m) {
            $status = $absensiRecords->has($m->nim) ? 'Selesai' : 'Belum';
            $students[] = [
                'nim' => $m->nim,
                'nama' => $m->nama,
                'status' => $status,
            ];
        }
        // 4. Get Berita Acara record for catatan (from dosen wali)
        $beritaAcara = DB::table('berita_acaras')
            ->where('kelas', $perwalian->kelas)
            ->where('tanggal_perwalian', $perwalian->Tanggal)
            ->first();
        $catatan = $beritaAcara->catatan_feedback ?? null;
        // 5. Return the detailed histori blade
        return view('dosen.detailed_histori', [
            'perwalian' => $perwalian,
            'students' => $students,
            'catatan' => $catatan,
        ]);
    }

    /**
     * Generate and download the Perwalian PDF.
     * The PDF will have:
     *   - Page 1: Agenda Perwalian (details & agenda_perwalian)
     *   - Page 2: Berita Acara Perwalian (dosen wali's report/catatan)
     *   - Page 3 and onward: Daftar Mahasiswa Absensi
     */
    public function printBeritaAcara($id)
    {
        // 1. Find the Perwalian record by ID
        $perwalian = Perwalian::find($id);
        if (!$perwalian) {
            return redirect()->route('dosen.histori')->with('error', 'Perwalian not found.');
        }
        // 2. Get Mahasiswa records for this Perwalian
        $mahasiswaRecords = DB::table('mahasiswa')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->orderBy('nama')
            ->get();
        $students = [];
        foreach ($mahasiswaRecords as $m) {
            $students[] = [
                'nim' => $m->nim,
                'nama' => $m->nama,
            ];
        }
        // 3. Get BeritaAcara record (dosen wali's report) if exists
        $beritaAcara = DB::table('berita_acaras')
            ->where('kelas', $perwalian->kelas)
            ->where('tanggal_perwalian', $perwalian->Tanggal)
            ->first();
        // 4. Get Absensi records for mahasiswa (for the attendance table)
        $absensiRecords = DB::table('absensi')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->get()
            ->keyBy('nim');
        // 5. Prepare data for the PDF view
        $pdfData = [
            'perwalian' => $perwalian,
            'students'  => $students,
            'absensi'   => $absensiRecords,
            'beritaAcara' => $beritaAcara, // contains catatan_feedback and other info
        ];
        // 6. Load the PDF view (the view handles page breaks)
        $pdf = PDF::loadView('dosen.berita_acara_pdf', $pdfData)
            ->setPaper('a4', 'portrait');
        return $pdf->download('Perwalian_' . $perwalian->kelas . '_' . $perwalian->Tanggal . '.pdf');
    }
}