<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SetPerwalianController extends Controller
{
    // Existing methods...

    /**
     * Display the History page.
     *
     * @return \Illuminate\View\View
     */
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

        // 2. Retrieve all scheduled Perwalian for this dosen
        $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->where('Status', 'Scheduled')
            ->get();

        // 3. Filter by search text (if any)
        $searchTerm = $request->input('search');
        if ($searchTerm) {
            $lowerSearch = mb_strtolower($searchTerm);
            $perwalianRecords = $perwalianRecords->filter(function ($item) use ($lowerSearch) {
                // Match search against the 'kelas', 'nama', or the raw date string
                $kelas = mb_strtolower($item->kelas);
                $nama = mb_strtolower($item->nama);
                $tanggal = $item->Tanggal; // e.g. '2025-01-20'

                return (str_contains($kelas, $lowerSearch) ||
                        str_contains($nama, $lowerSearch) ||
                        str_contains($tanggal, $lowerSearch));
            })->values();
        }

        // 4. Categorize each date into Semester Baru, Sebelum UTS, or Sebelum UAS
        //    based on your new date rules:
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

            // Decide category based on new ranges
            $category = $this->determineCategory($month, $day);

            if ($category === 'semester_baru') {
                $semesterBaru[] = $record;
            } elseif ($category === 'sebelum_uts') {
                $sebelumUts[] = $record;
            } elseif ($category === 'sebelum_uas') {
                $sebelumUas[] = $record;
            } else {
                // Default assignment
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
     * based on the new rules provided.
     */
    private function determineCategory($month, $day)
    {
        // Semester Genap: (months 1 to 5)
        if ($month == 1) {
            // Semester Baru: 1 Januari – 31 Januari
            return 'semester_baru';
        }
        if ($month == 2) {
            if ($day == 1) {
                return 'semester_baru';
            }
            if ($day >= 2 && $day <= 29) { // assume February days (leap-year friendly)
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
            // For April and for May up to 19
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

        // For any date not matching the above rules, default to 'semester_baru'
        return 'semester_baru';
    }

    // -------------------------------------------
    // The rest of your existing methods below
    // (index, getCalendar, store, destroy, etc.)
    // -------------------------------------------

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
            $user = Dosen::where('username', $username)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to set a perwalian date.'
                ], 401);
            }

            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $validatedData['selectedClass'])
                ->first();

            if ($existingPerwalian) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a scheduled perwalian request for this class. Use the Edit option to delete and request again.'
                ], 400);
            }

            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user->nip,
                'Tanggal' => Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'),
                'Status' => 'Scheduled',
                'nama' => $user->nama,
                'kelas' => $validatedData['selectedClass'],
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
                'ID_Perwalian' => $perwalian->getKey()
            ]);

            $nim = session('user')['nim'] ?? null;
            Notifikasi::create([
                'Pesan' => "Perwalian scheduled for " . $validatedData['selectedDate'] . " (Class: " . $validatedData['selectedClass'] . ")",
                'NIM' => $nim,
                'Id_Perwalian' => $perwalian->getKey(),
                'nama' => $user->nama,
            ]);

            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] .
                ' by dosen NIP: ' . $user->nip .
                ' for class: ' . $validatedData['selectedClass']);

            $scheduledDatesByClass = [];
            $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
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
            Log::error('Failed to set Perwalian date: ' . $e->getMessage());
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

            Notifikasi::where('Id_Perwalian', $existingPerwalian->getKey())->delete();
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
}
