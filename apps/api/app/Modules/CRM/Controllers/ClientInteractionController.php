<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientInteractionController extends Controller
{
    public function index(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $interactions = $client->interactions()
            ->with(['user', 'contact'])
            ->orderBy('interaction_date', 'desc')
            ->get();

        return response()->json([
            'data' => $interactions->map(fn ($i) => [
                'id' => $i->id,
                'channel' => $i->channel,
                'summary' => $i->summary,
                'interaction_date' => $i->interaction_date->toIso8601String(),
                'next_follow_up_date' => $i->next_follow_up_date?->format('Y-m-d'),
                'user' => new UserResource($i->user),
                'contact' => $i->contact ? ['id' => $i->contact->id, 'name' => $i->contact->name] : null,
            ]),
        ]);
    }

    public function store(Request $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'channel' => ['required', Rule::in(['meeting', 'call', 'whatsapp', 'email', 'site_visit'])],
            'summary' => ['required', 'string'],
            'interaction_date' => ['required', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
        ]);

        $interaction = $client->interactions()->create([
            'contact_id' => $validated['contact_id'] ?? null,
            'user_id' => $request->user()->id,
            'channel' => $validated['channel'],
            'summary' => $validated['summary'],
            'interaction_date' => $validated['interaction_date'],
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
        ]);

        AuditLog::log('client.interaction_logged', $client, [
            'channel' => $interaction->channel,
            'interaction_id' => $interaction->id,
        ]);

        return response()->json([
            'message' => 'تم تسجيل المتابعة بنجاح',
            'interaction' => $interaction,
        ], 201);
    }
}
