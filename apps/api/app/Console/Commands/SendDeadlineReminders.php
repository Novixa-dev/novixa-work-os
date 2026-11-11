<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Scheduled hourly. Notifies assignees (and project leads) about tasks that are due soon,
 * due today, or overdue. Idempotent: each (user, task, kind, day-for-overdue) is sent once.
 */
class SendDeadlineReminders extends Command
{
    protected $signature = 'workos:deadline-reminders {--days=2 : Warn this many days before the deadline}';

    protected $description = 'Send due-soon / overdue task notifications';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $today = now()->startOfDay();
        $sent = 0;

        $tasks = Task::query()
            ->with(['assignee', 'project.lead'])
            ->whereNotNull('due_date')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNull('archived_at')
            ->where('due_date', '<=', $today->copy()->addDays($days))
            ->get();

        foreach ($tasks as $task) {
            $daysLeft = (int) $today->diffInDays($task->due_date->startOfDay(), false);
            $recipients = collect([$task->assignee, $task->project?->lead])
                ->filter(fn ($u) => $u && $u->is_active)->unique('id');

            foreach ($recipients as $user) {
                if ($daysLeft < 0) {
                    $kind = 'task.overdue';
                    $key = "overdue:{$task->id}:{$today->toDateString()}"; // once per day
                    $ar = 'متأخرة منذ '.abs($daysLeft)." يوم: {$task->code} {$task->title_ar}";
                    $en = 'Overdue by '.abs($daysLeft)." day(s): {$task->code}";
                } elseif ($daysLeft === 0) {
                    $kind = 'task.due_today';
                    $key = "due_today:{$task->id}";
                    $ar = "موعد تسليمها اليوم: {$task->code} {$task->title_ar}";
                    $en = "Due today: {$task->code}";
                } else {
                    $kind = 'task.due_soon';
                    $key = "due_soon:{$task->id}";
                    $ar = "تقترب من الموعد ($daysLeft يوم): {$task->code} {$task->title_ar}";
                    $en = "Due in {$daysLeft} day(s): {$task->code}";
                }

                try {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => $kind,
                        'title_ar' => $ar,
                        'title_en' => $en,
                        'link' => "/tasks?code={$task->code}",
                        'dedupe_key' => $key,
                    ]);
                    $sent++;
                } catch (UniqueConstraintViolationException) {
                    // already notified for this key
                }
            }
        }

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
