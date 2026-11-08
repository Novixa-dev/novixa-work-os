<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Modules\CRM\Resources\ClientResource;
use App\Support\Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::with(['accountManager', 'contacts'])
            ->withCount(['opportunities', 'projects']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->input('industry'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', Like::contains($search))
                    ->orWhere('name_en', 'like', Like::contains($search))
                    ->orWhere('city', 'like', Like::contains($search));
            });
        }

        $clients = $query->orderBy('name_ar')->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => ClientResource::collection($clients->items()),
            'meta' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ],
        ]);
    }

    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $client->load([
            'accountManager', 'contacts', 'opportunities.product',
            'interactions.user', 'projects.lead',
        ]);

        return response()->json([
            'client' => new ClientResource($client),
            'interactions' => $client->interactions,
            'opportunities' => $client->opportunities,
            'projects' => $client->projects,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['company', 'individual'])],
            'industry' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['lead', 'prospect', 'active_client', 'inactive'])],
            'website' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'account_manager_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'primary_contact_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $client = DB::transaction(function () use ($validated) {
            $client = Client::create($validated);

            if (! empty($validated['primary_contact_name'])) {
                $client->contacts()->create([
                    'name' => $validated['primary_contact_name'],
                    'phone' => $validated['primary_contact_phone'] ?? null,
                    'email' => $validated['primary_contact_email'] ?? null,
                    'is_primary' => true,
                ]);
            }

            AuditLog::log('client.created', $client, [
                'name_ar' => $client->name_ar,
                'status' => $client->status,
            ]);

            return $client;
        });

        $client->load(['accountManager', 'contacts']);

        return response()->json([
            'message' => 'تم إنشاء سجل العميل بنجاح / Client record created successfully',
            'client' => new ClientResource($client),
        ], 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(['company', 'individual'])],
            'industry' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', Rule::in(['lead', 'prospect', 'active_client', 'inactive'])],
            'website' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'account_manager_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        AuditLog::log('client.updated', $client, [
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'message' => 'تم تحديث بيانات العميل بنجاح',
            'client' => new ClientResource($client),
        ]);
    }
}
