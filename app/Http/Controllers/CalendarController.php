<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CalendarController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:akademik,bem',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx,xls|max:12000',
        ]);

        // Hapus kalender lama berdasarkan tipe
        $existingCalendar = Calendar::where('type', $request->type)->first();
        if ($existingCalendar) {
            Storage::delete($existingCalendar->file_path); // Hapus file lama
            $existingCalendar->delete(); // Hapus dari database
        }

        // Simpan file baru
        $filePath = $request->file('file')->store('calendars', 'public');

        // Simpan detail ke database
        Calendar::create([
            'type' => $request->type,
            'file_path' => $filePath,
        ]);

        return back()->with('success', ucfirst($request->type) . ' kalender berhasil diunggah!');
    }
}
