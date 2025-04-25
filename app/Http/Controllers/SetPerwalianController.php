<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use App\Services\StudentSyncService;
use App\Models\Absensi;
use App\Models\BeritaAcara;
use App\Notifications\UniversalNotification;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class SetPerwalianController extends Controller
{
    protected $studentSyncService;

    public function __construct(StudentSyncService $studentSyncService)
    {
        $this->studentSyncService = $studentSyncService;
    }

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
                ->whereIn('Status', ['Scheduled', 'Presented']) // Show both Scheduled and Presented
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
        $notifications = Notifikasi::with('perwalian')->get();
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
            $dosenWaliIds = $notifications->pluck('perwalian.ID_Dosen_Wali')->unique()->filter();
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

        $user = session('user');
        if (!$user) {
            return response()->json(['error' => 'You must be logged in to access this data.'], 401);
        }

        $perwalians = Perwalian::where('ID_Dosen_Wali', $user['nip'])->get();

        $events = $perwalians->map(function ($perwalian) {
            return [
                'title' => "Perwalian {$perwalian->kelas} (Status: {$perwalian->Status})",
                'start' => $perwalian->Tanggal,
                'id' => $perwalian->ID_Perwalian,
                'status' => $perwalian->Status,
            ];
        });

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
            'events' => $events, // Include events for FullCalendar
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
    Log::info('Store request received', ['request' => $request->all(), 'headers' => $request->headers->all()]);
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
        $username = session('user')['username'] ?? null;
        $user = session('user');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to set a perwalian date.'
            ], 401);
        }

        $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user['nip'])
            ->where('kelas', $validatedData['selectedClass'])
            ->where('Tanggal', Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'))
            ->first();
        if ($existingPerwalian) {
            return response()->json([
                'success' => false,
                'message' => 'A perwalian session is already scheduled for this class on this date. Use the Edit option to delete and request again.'
            ], 400);
        }

        $dosenWali = DB::table('dosen_wali')->where('username', $user['username'])->first();
        if (!$dosenWali) {
            Log::error('Dosen Wali record not found for user in SetPerwalianController@store:', [
                'username' => $user['username'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Dosen Wali record not found for this lecturer.',
            ], 404);
        }

        $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
        $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));
        $kelasAngkatanMap = [];
        foreach ($kelasList as $index => $kelas) {
            $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
            $kelasAngkatanMap[$kelas] = $angkatan;
        }

        $angkatan = $kelasAngkatanMap[$validatedData['selectedClass']] ?? null;
        if (!$angkatan) {
            Log::error('Angkatan not found for kelas in SetPerwalianController@store:', [
                'kelas' => $validatedData['selectedClass'],
                'kelas_list' => $kelasList,
                'angkatan_list' => $angkatanList,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Angkatan not found for the specified kelas.',
            ], 400);
        }

        $selectedDate = Carbon::parse($validatedData['selectedDate']);
        $month = (int) $selectedDate->month;
        $day = (int) $selectedDate->day;
        Log::info('Extracted month and day from selectedDate', [
            'selectedDate' => $validatedData['selectedDate'],
            'month' => $month,
            'day' => $day,
        ]);

        $category = $this->determineCategory($month, $day);
        Log::info('Category determined from selectedDate', [
            'selectedDate' => $validatedData['selectedDate'],
            'month' => $month,
            'day' => $day,
            'category' => $category,
        ]);

        $categoryToKeteranganMap = [
            'semester_baru' => 'Semester Baru',
            'sebelum_uts' => 'Sebelum UTS',
            'sebelum_uas' => 'Sebelum UAS',
        ];
        $keterangan = $categoryToKeteranganMap[$category] ?? 'Semester Baru';
        Log::info('Keterangan mapped from category', [
            'category' => $category,
            'keterangan' => $keterangan,
        ]);

        $perwalian = DB::transaction(function () use ($user, $validatedData, $selectedDate, $angkatan, $keterangan) {
            $syncYear = $angkatan;
            $currentSem = 2;
            $students = $this->studentSyncService->fetchStudents($user['pegawai_id'], $syncYear, $currentSem, $validatedData['selectedClass']);

            Log::info('Students fetched for Perwalian in SetPerwalianController@store', [
                'class' => $validatedData['selectedClass'],
                'student_count' => count($students),
                'students' => $students,
            ]);

            if (empty($students)) {
                Log::warning('No students fetched for class in SetPerwalianController@store', [
                    'class' => $validatedData['selectedClass'],
                    'dosen_id' => $user['pegawai_id'],
                    'sync_year' => $syncYear,
                    'semester' => $currentSem,
                ]);
            }

            $students = array_map(function ($student) {
                return [
                    'nim' => $student['nim'],
                    'nama' => $student['nama'],
                ];
            }, $students);

            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user['nip'],
                'Tanggal' => $selectedDate->format('Y-m-d'),
                'Status' => 'Scheduled',
                'nama' => $user['nama'],
                'kelas' => $validatedData['selectedClass'],
                'angkatan' => $angkatan,
                'role' => 'mahasiswa',
                'keterangan' => $keterangan,
            ]);

            if (!$perwalian) {
                Log::error('Failed to create Perwalian record');
                throw new \Exception('Failed to create Perwalian record.');
            }
            Log::info('Perwalian created:', [
                'perwalian' => $perwalian->toArray(),
                'ID_Perwalian' => $perwalian->ID_Perwalian,
            ]);

            foreach ($students as $student) {
                $nim = $student['nim'];
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
                        'nama' => $student['nama'],
                        'kelas' => $validatedData['selectedClass'],
                        'ID_Dosen' => $user['pegawai_id'],
                    ]
                );

                $mahasiswa->ID_Perwalian = $perwalian->ID_Perwalian;
                $mahasiswa->save();

                Log::info('Mahasiswa record after update:', [
                    'nim' => $mahasiswa->nim,
                    'ID_Perwalian' => $mahasiswa->ID_Perwalian,
                ]);
            }

            return $perwalian;
        });

        // Debug: Log the Perwalian ID to ensure it's correct
        Log::info('Perwalian transaction completed', [
            'perwalian_id' => $perwalian->ID_Perwalian,
        ]);

        // Send notifications after the transaction
        $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();
        Log::info('Mahasiswa records found for notification', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'mahasiswa_count' => $mahasiswas->count(),
            'mahasiswas' => $mahasiswas->pluck('nim')->toArray(),
        ]);

        if ($mahasiswas->isNotEmpty()) {
            $date = Carbon::parse($perwalian->Tanggal)->translatedFormat('j F Y');
            $message = "Perwalian scheduled for your class {$perwalian->kelas} on {$date} by {$perwalian->nama}.";
            $extraData = [
                'link' => route('mahasiswa_perwalian'),
                'type' => 'perwalian'
            ];

            foreach ($mahasiswas as $mahasiswa) {
                try {
                    $mahasiswa->notify(new UniversalNotification($message, $extraData));
                    Log::info("Notification sent to Mahasiswa NIM {$mahasiswa->nim}: {$message}");
                } catch (\Exception $e) {
                    Log::error("Failed to send notification to Mahasiswa NIM {$mahasiswa->nim}: {$e->getMessage()}", [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        } else {
            Log::warning('No Mahasiswa found to notify for Perwalian after transaction', [
                'perwalian_id' => $perwalian->ID_Perwalian,
            ]);
        }

        Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] .
            ' by dosen NIP: ' . $user['nip'] .
            ' for class: ' . $validatedData['selectedClass']);

        $scheduledDatesByClass = [];
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user['nip'])
            ->whereIn('Status', ['Scheduled', 'Presented'])
            ->get(['kelas', 'Tanggal']);
        foreach ($perwalianRecords as $record) {
            $date = Carbon::parse($record->Tanggal)->format('Y-m-d');
            $scheduledDatesByClass[$record->kelas][] = $date;
        }
        return response()->json([
            'success' => true,
            'message' => 'Perwalian date set successfully for ' .
                $validatedData['selectedDate'] .
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

        // Only allow deletion if the Perwalian is in "Scheduled" or "Presented" status
        $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->whereIn('Status', ['Scheduled', 'Presented'])
            ->where('kelas', $selectedClass)
            ->first();

        if (!$existingPerwalian) {
            return response()->json([
                'success' => false,
                'message' => 'No scheduled perwalian found for this class, or it has already been presented or completed.'
            ], 404);
        }

        // Perform deletion within a transaction
        DB::transaction(function () use ($selectedClass, $existingPerwalian, $user) {
            // Log Mahasiswa records before deletion
            $mahasiswasBefore = Mahasiswa::where('ID_Perwalian', $existingPerwalian->ID_Perwalian)->get();
            Log::info('Mahasiswa records before Perwalian deletion', [
                'perwalian_id' => $existingPerwalian->ID_Perwalian,
                'mahasiswa_count' => $mahasiswasBefore->count(),
                'mahasiswas' => $mahasiswasBefore->pluck('nim')->toArray(),
            ]);

            // Delete the Perwalian record first (this will trigger the observer)
            $existingPerwalian->delete();

            // Clear ID_Perwalian in Mahasiswa table for the affected class
            $updatedCount = Mahasiswa::where('kelas', $selectedClass)
                ->where('ID_Perwalian', $existingPerwalian->ID_Perwalian)
                ->update(['ID_Perwalian' => null]);

            Log::info('Cleared ID_Perwalian for students in class:', [
                'class' => $selectedClass,
                'perwalian_id' => $existingPerwalian->ID_Perwalian,
                'updated_count' => $updatedCount,
            ]);
        });

        Log::info('Perwalian deleted for dosen NIP: ' . $user->nip . ' for class: ' . $selectedClass);

        $scheduledDatesByClass = [];
        $scheduledClasses = [];
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->whereIn('Status', ['Scheduled', 'Presented'])
            ->get(['kelas', 'Tanggal']);
        foreach ($perwalianRecords as $record) {
            $date = Carbon::parse($record->Tanggal)->format('Y-m-d');
            $scheduledDatesByClass[$record->kelas][] = $date;
            $scheduledClasses[] = $record->kelas;
        }

        $message = 'Perwalian request deleted for class ' . $selectedClass . '. You can now create a new request.';

        return response()->json([
            'success' => true,
            'message' => $message,
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

    public function histori(Request $request)
    {
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

        // Retrieve all Perwalian records for this dosen
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->orderBy('Tanggal', 'desc') // Order by date for better history view
            ->where('Status', 'Completed')
            ->get();

        $searchTerm = $request->input('search');
        $selectedCategory = $request->input('category');

        // First, if a search term is provided, filter by keyword
        if ($searchTerm) {
            $lowerSearch = mb_strtolower($searchTerm);
            $searchTokens = array_filter(explode(' ', $lowerSearch));

            $perwalianRecords = $perwalianRecords->filter(function ($item) use ($searchTokens) {
                $kelas = mb_strtolower($item->kelas);
                $tanggal = mb_strtolower(Carbon::parse($item->Tanggal)->translatedFormat('l, d F Y'));

                foreach ($searchTokens as $token) {
                    if (!str_contains($kelas, $token) && !str_contains($tanggal, $token)) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        // Initialize variables for the view
        $semesterBaru = [];
        $sebelumUts = [];
        $sebelumUas = [];
        $singleRecords = [];
        $categoryTitle = '';
        $showSingleCategory = false;

        // If a category is selected, filter records and prepare for single-column display
        if ($selectedCategory) {
            $showSingleCategory = true;
            $perwalianRecords = $perwalianRecords->filter(function ($item) use ($selectedCategory) {
                $dateObj = Carbon::parse($item->Tanggal);
                $category = $this->determineCategory($dateObj->month, $dateObj->day);
                return $category === $selectedCategory;
            })->values();

            // Set the category title and single records array
            switch ($selectedCategory) {
                case 'semester_baru':
                    $categoryTitle = 'Semester Baru';
                    break;
                case 'sebelum_uts':
                    $categoryTitle = 'Sebelum UTS';
                    break;
                case 'sebelum_uas':
                    $categoryTitle = 'Sebelum UAS';
                    break;
            }
            $singleRecords = $perwalianRecords->toArray();
        } else {
            // If no category is selected, categorize all records into three arrays
            foreach ($perwalianRecords as $record) {
                $dateObj = Carbon::parse($record->Tanggal);
                $category = $this->determineCategory($dateObj->month, $dateObj->day);
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
        }

        return view('dosen.histori', [
            'semesterBaru' => $semesterBaru,
            'sebelumUts' => $sebelumUts,
            'sebelumUas' => $sebelumUas,
            'singleRecords' => $singleRecords,
            'categoryTitle' => $categoryTitle,
            'selectedCategory' => $selectedCategory,
            'showSingleCategory' => $showSingleCategory,
        ]);
    }


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

    public function detailedHistori($id)
    {
        $username = session('user')['username'] ?? null;
        if (!$username) {
            Log::warning('No username found in session for detailedHistori', ['session' => session()->all()]);
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }
    
        $user = Dosen::where('username', $username)->first();
        if (!$user) {
            Log::error('No Dosen found for username in detailedHistori', ['username' => $username]);
            return redirect()->route('login')->with('error', 'User not found or not authorized.');
        }
    
        // Find the Perwalian record by ID and ensure it belongs to the dosen
        $perwalian = Perwalian::where('ID_Perwalian', $id)
            ->where('ID_Dosen_Wali', $user->nip)
            ->first();
        if (!$perwalian) {
            return redirect()->route('dosen.histori')->with('error', 'Perwalian not found.');
        }
    
        // Get all mahasiswa records for this Perwalian
        $mahasiswaRecords = DB::table('mahasiswa')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->orderBy('nama')
            ->get();
    
        // Fetch Absensi records for these students (keyed by nim)
        $absensiRecords = DB::table('absensi')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->get()
            ->keyBy('nim');
    
        // Build students array using actual status from absensi record if it exists
        $students = [];
        foreach ($mahasiswaRecords as $m) {
            $absRecord = $absensiRecords->get($m->nim);
            $status = $absRecord ? $absRecord->status_kehadiran : 'Belum';
            $students[] = [
                'nim'   => $m->nim,
                'nama'  => $m->nama,
                'status'=> $status,
            ];
        }
    
        // Get Berita Acara record for catatan (from dosen wali)
        // $beritaAcara = DB::table('berita_acaras')
        //     ->where('kelas', $perwalian->kelas)
        //     ->where('tanggal_perwalian', $perwalian->Tanggal)
        //     ->where('user_id', $user->user_id)
        //     ->first();

        $beritaAcara = DB::table('berita_acaras')
        ->where('kelas', $perwalian->kelas)
        ->where('tanggal_perwalian', $perwalian->Tanggal)
        ->first();
            

        $catatan = $beritaAcara->catatan_feedback ?? null;
        return view('dosen.detailed_histori', [
            'perwalian' => $perwalian,
            'students'  => $students,
            'catatan'   => $catatan,
        ]);
    }

    public function printBeritaAcara($id)
    {
        $username = session('user')['username'] ?? null;
        if (!$username) {
            Log::warning('No username found in session for printBeritaAcara', ['session' => session()->all()]);
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Dosen::where('username', $username)->first();
        if (!$user) {
            Log::error('No Dosen found for username in printBeritaAcara', ['username' => $username]);
            return redirect()->route('login')->with('error', 'User not found or not authorized.');
        }

        // Find the Perwalian record by ID
        $perwalian = Perwalian::where('ID_Perwalian', $id)
            ->where('ID_Dosen_Wali', $user->nip)
            ->first();
        if (!$perwalian) {
            return redirect()->route('dosen.histori')->with('error', 'Perwalian not found.');
        }
        Log::Info('Gote her1');

        // Ensure the Perwalian is in "Completed" status
        if ($perwalian->Status !== 'Completed') {
            return redirect()->route('dosen.histori')->with('error', 'Berita Acara can only be printed for completed Perwalian sessions.');
        }

        Log::Info('Gote her2');

        // Get Mahasiswa records for this Perwalian
        $mahasiswaRecords = DB::table('mahasiswa')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->orderBy('nama')
            ->get();

            Log::Info('Gote her3');

        $students = [];
        foreach ($mahasiswaRecords as $m) {
            $students[] = [
                'nim' => $m->nim,
                'nama' => $m->nama,
            ];
        }

        Log::Info('Gote her4');

        // Get BeritaAcara record (dosen wali's report)
        $beritaAcara = DB::table('berita_acaras')
            ->where('kelas', $perwalian->kelas)
            ->where('tanggal_perwalian', $perwalian->Tanggal)
            ->where('dosen_wali', $user->username)
            ->first();

            

        if (!$beritaAcara) {
            return redirect()->route('dosen.histori')->with('error', 'Berita Acara not found for this Perwalian session.');
        }

        Log::Info('Gote her6');


        // Get Absensi records for mahasiswa (for the attendance table)
        $absensiRecords = DB::table('absensi')
            ->where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->get()
            ->keyBy('nim');

            Log::Info('Gote her7');

        // Prepare data for the PDF view
        $pdfData = [
            'perwalian' => $perwalian,
            'students' => $students,
            'absensi' => $absensiRecords,
            'beritaAcara' => $beritaAcara,
        ];

        
        Log::Info('Gote her8');

        // Load the PDF view
        $pdf = PDF::loadView('dosen.berita_acara_pdf', $pdfData)
            ->setPaper('a4', 'portrait');

            Log::Info('Gote her9');

        return $pdf->download('Perwalian_' . $perwalian->kelas . '_' . $perwalian->Tanggal . '.pdf');
    }
}