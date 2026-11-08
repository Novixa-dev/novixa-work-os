<?php

namespace App\Modules\Work\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskDependencyService
{
    /**
     * Check if adding a dependency (task depends on dependsOnTask) would create a cycle.
     * In other words: Does dependsOnTask already depend (directly or indirectly) on task?
     */
    public function wouldCreateCycle(int $taskId, int $dependsOnTaskId): bool
    {
        if ($taskId === $dependsOnTaskId) {
            return true;
        }

        // DFS from $dependsOnTaskId to see if we can reach $taskId
        $visited = [];
        return $this->canReach($dependsOnTaskId, $taskId, $visited);
    }

    protected function canReach(int $currentTaskId, int $targetTaskId, array &$visited): bool
    {
        if ($currentTaskId === $targetTaskId) {
            return true;
        }

        if (isset($visited[$currentTaskId])) {
            return false;
        }

        $visited[$currentTaskId] = true;

        // Find all tasks that $currentTaskId depends on
        $dependencies = DB::table('task_dependencies')
            ->where('task_id', $currentTaskId)
            ->pluck('depends_on_task_id');

        foreach ($dependencies as $depId) {
            if ($this->canReach((int) $depId, $targetTaskId, $visited)) {
                return true;
            }
        }

        return false;
    }
}
