<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\KnowledgePage;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = [];

        // 1. Tasks
        $taskQuery = Task::where(function ($q) use ($query) {
            $q->where('code', 'like', "%{$query}%")
              ->orWhere('title_ar', 'like', "%{$query}%")
              ->orWhere('title_en', 'like', "%{$query}%");
        });

        if ($user->isGuest()) {
            $taskQuery->where(function ($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhereHas('project.members', fn ($sub) => $sub->where('user_id', $user->id));
            });
        }

        $tasks = $taskQuery->take(5)->get();
        foreach ($tasks as $task) {
            $results[] = [
                'type' => 'task',
                'id' => $task->id,
                'title' => "{$task->code}: {$task->title_ar}",
                'subtitle' => $task->title_en,
                'url' => "/tasks?code={$task->code}",
                'badge' => $task->status,
            ];
        }

        // 2. Projects
        $projQuery = Project::where(function ($q) use ($query) {
            $q->where('code', 'like', "%{$query}%")
              ->orWhere('name_ar', 'like', "%{$query}%")
              ->orWhere('name_en', 'like', "%{$query}%");
        });

        if ($user->isGuest()) {
            $projQuery->whereHas('members', fn ($sub) => $sub->where('user_id', $user->id));
        }

        $projects = $projQuery->take(5)->get();
        foreach ($projects as $proj) {
            $results[] = [
                'type' => 'project',
                'id' => $proj->id,
                'title' => "{$proj->code}: {$proj->name_ar}",
                'subtitle' => $proj->name_en,
                'url' => "/projects?code={$proj->code}",
                'badge' => $proj->status,
            ];
        }

        // 3. Products
        $products = Product::where('name_ar', 'like', "%{$query}%")
            ->orWhere('name_en', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->take(4)
            ->get();

        foreach ($products as $prod) {
            $results[] = [
                'type' => 'product',
                'id' => $prod->id,
                'title' => $prod->name_ar,
                'subtitle' => $prod->name_en,
                'url' => "/products/{$prod->slug}",
                'badge' => $prod->lifecycle_status,
            ];
        }

        // 4. Clients (non-guest only)
        if (!$user->isGuest()) {
            $clients = Client::where('name_ar', 'like', "%{$query}%")
                ->orWhere('name_en', 'like', "%{$query}%")
                ->orWhere('city', 'like', "%{$query}%")
                ->take(4)
                ->get();

            foreach ($clients as $client) {
                $results[] = [
                    'type' => 'client',
                    'id' => $client->id,
                    'title' => $client->name_ar,
                    'subtitle' => $client->city,
                    'url' => "/crm/clients/{$client->id}",
                    'badge' => $client->status,
                ];
            }
        }

        // 5. Knowledge Pages
        $pages = KnowledgePage::where('title_ar', 'like', "%{$query}%")
            ->orWhere('title_en', 'like', "%{$query}%")
            ->take(3)
            ->get();

        foreach ($pages as $page) {
            $results[] = [
                'type' => 'knowledge',
                'id' => $page->id,
                'title' => $page->title_ar,
                'subtitle' => $page->category,
                'url' => "/knowledge/{$page->slug}",
                'badge' => $page->category,
            ];
        }

        return response()->json([
            'results' => $results,
        ]);
    }
}
