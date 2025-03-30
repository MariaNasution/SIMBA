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
            $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
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
                return response()->json(['success' => false, 'message' => 'You must be logged in to set a perwalian date.'], 401);
            }

            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $validatedData['selectedClass'])
                ->first();

            if ($existingPerwalian) {
                return response()->json(['success' => false, 'message' => 'You already have a scheduled perwalian request for this class. Use the Edit option to delete and request again.'], 400);
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
                return response()->json(['success' => false, 'message' => 'Failed to create Perwalian record.'], 500);
            }

            Log::info('Perwalian created:', ['perwalian' => $perwalian->toArray(), 'ID_Perwalian' => $perwalian->getKey()]);

            $nim = session('user')['nim'] ?? null;
            Notifikasi::create([
                'Pesan' => "Perwalian scheduled for " . $validatedData['selectedDate'] . " (Class: " . $validatedData['selectedClass'] . ")",
                'NIM' => $nim,
                'Id_Perwalian' => $perwalian->getKey(),
                'nama' => $user->nama,
            ]);

            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] . ' by dosen NIP: ' . $user->nip . ' for class: ' . $validatedData['selectedClass']);

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
                'message' => 'Perwalian date set successfully for ' . $validatedData['selectedDate'] . ' (Class: ' . $validatedData['selectedClass'] . ')',
                'scheduledDatesByClass' => $scheduledDatesByClass,
                'scheduledClasses' => array_keys($scheduledDatesByClass),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set Perwalian date: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to set Perwalian date: ' . $e->getMessage()], 500);
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
                return response()->json(['success' => false, 'message' => 'You must be logged in to delete a perwalian.'], 401);
            }

            $selectedClass = $request->input('selectedClass');
            if (!$selectedClass) {
                return response()->json(['success' => false, 'message' => 'No class selected for deletion.'], 400);
            }

            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $selectedClass)
                ->first();

            if (!$existingPerwalian) {
                return response()->json(['success' => false, 'message' => 'No scheduled perwalian found for this class.'], 404);
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
            return response()->json(['success' => false, 'message' => 'Failed to delete Perwalian: ' . $e->getMessage()], 500);
        }
    }
}