<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Semester; // Import model Semester
use App\Models\AbsenGuru; // Import model AbsenGuru
use App\Models\AbsenSiswa; // Import model AbsenSiswa
use Filament\Notifications\Notification;

class DeleteAbsensiBySemester implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $semesterId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $semesterId)
    {
        $this->semesterId = $semesterId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $semester = Semester::find($this->semesterId);

        if (!$semester) {
            //Notifikasi jika semester tidak ditemukan (opsional)
            Notification::make('')
                ->title('Gagal menghapus data absensi: Semester tidak ditemukan.')
                ->danger()
                ->sendToDatabase(auth()->user()); // Kirim ke user yang memicu action
            return;
        }

        // Hapus data AbsenGuru
        AbsenGuru::where('semester_id', $this->semesterId)->delete();

        // Hapus data AbsenSiswa
        AbsenSiswa::where('semester_id', $this->semesterId)->delete();

        // Opsional: Kirim notifikasi sukses setelah selesai
        // $recipient = auth()->user();

        // Notification::make()
        //     ->title('Delete successfully')
        //     ->sendToDatabase($recipient);
    }

    // public function failed(\Throwable $exception): void
    // {
    //     $semester = Semester::find($this->semesterId);
    //     $semesterName = $semester ? $semester->semester : 'Unknown';

    //     Notification::make('')
    //         ->title('Gagal menghapus data absensi semester ' . $semesterName)
    //         ->body('Terjadi kesalahan: ' . $exception->getMessage())
    //         ->danger()
    //         ->sendToDatabase(auth()->user());
    // }
}
