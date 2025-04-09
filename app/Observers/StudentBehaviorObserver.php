<?php

namespace App\Observers;

use App\Models\StudentBehavior;
use App\Services\NotificationService;

class StudentBehaviorObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the StudentBehavior "created" event.
     *
     * @param  \App\Models\StudentBehavior  $behavior
     * @return void
     */
    public function created(StudentBehavior $behavior)
    {
        // When a new behavior is added, notify the student by their NIM.
        $this->notificationService->createNotification(
            $behavior->nim,               // Assuming 'nim' is used as the identifier
            'pelanggaran',                // Type/category of the notification
            'Catatan perilaku baru telah ditambahkan.', // Notification message
            ['action' => 'create', 'data' => $behavior->toArray()]
        );
    }

    /**
     * Handle the StudentBehavior "updated" event.
     *
     * @param  \App\Models\StudentBehavior  $behavior
     * @return void
     */
    public function updated(StudentBehavior $behavior)
    {
        $this->notificationService->createNotification(
            $behavior->nim,
            'pelanggaran',
            'Catatan perilaku telah diubah.',
            ['action' => 'update', 'data' => $behavior->toArray()]
        );
    }

    /**
     * Handle the StudentBehavior "deleted" event.
     *
     * @param  \App\Models\StudentBehavior  $behavior
     * @return void
     */
    public function deleted(StudentBehavior $behavior)
    {
        $this->notificationService->createNotification(
            $behavior->nim,
            'pelanggaran',
            'Catatan perilaku telah dihapus.',
            ['action' => 'delete', 'data' => $behavior->toArray()]
        );
    }
}
