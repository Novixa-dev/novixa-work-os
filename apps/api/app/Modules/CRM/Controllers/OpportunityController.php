<?php

namespace App\Modules\CRM\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Opportunity;
use App\Models\Project;
use App\Modules\CRM\Resources\OpportunityResource;
use App\Modules\Projects\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Opportunity::class);

        $query = Opportunity::with(['client', 'product', 'owner']);

        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->input('owner_id'));
        }

        $opportunities = $query->orderBy('expected_close_date', 'asc')->get();

        return response()->json([
            'data' => OpportunityResource::collection($opportunities),
        ]);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('view', $opportunity);

        $opportunity->load(['client', 'product', 'owner', 'wonDeliveryProject']);

        return response()->json([
            'opportunity' => new OpportunityResource($opportunity),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Opportunity::class);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'stage' => ['required', Rule::in(['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $opportunity = Opportunity::create($validated);

        AuditLog::log('opportunity.created', $opportunity, [
            'title_ar' => $opportunity->title_ar,
            'client_id' => $opportunity->client_id,
            'estimated_value' => $opportunity->estimated_value,
        ]);

        $opportunity->load(['client', 'product', 'owner']);

        return response()->json([
            'message' => 'تم إنشاء الفرصة بنجاح / Opportunity created successfully',
            'opportunity' => new OpportunityResource($opportunity),
        ], 201);
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'stage' => ['sometimes', 'required', Rule::in(['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $opportunity->update($validated);

        AuditLog::log('opportunity.updated', $opportunity, [
            'updated_fields' => array_keys($validated),
        ]);

        $opportunity->load(['client', 'product', 'owner']);

        return response()->json([
            'message' => 'تم تحديث الفرصة بنجاح',
            'opportunity' => new OpportunityResource($opportunity),
        ]);
    }

    public function updateStage(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'stage' => ['required', Rule::in(['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])],
        ]);

        $prevStage = $opportunity->stage;
        $opportunity->stage = $validated['stage'];
        $opportunity->save();

        if ($opportunity->stage === 'won' && $prevStage !== 'won') {
            // Trigger deal won notification to owner/managers
            Notification::create([
                'user_id' => $opportunity->owner_id ?? auth()->id(),
                'type' => 'deal.won',
                'title_ar' => "صفقة رابحة جديدة! 🎉 {$opportunity->title_ar}",
                'title_en' => "Deal Won! 🎉 {$opportunity->title_ar}",
                'body_ar' => "تم إغلاق الصفقة بنجاح بقيمة {$opportunity->estimated_value} {$opportunity->currency}",
                'body_en' => "Opportunity won for {$opportunity->estimated_value} {$opportunity->currency}",
                'link' => '/crm/opportunities',
            ]);
        }

        AuditLog::log('opportunity.stage_changed', $opportunity, [
            'from' => $prevStage,
            'to' => $opportunity->stage,
        ]);

        return response()->json([
            'message' => 'تم تحديث مرحلة الفرصة بنجاح',
            'opportunity' => new OpportunityResource($opportunity),
        ]);
    }

    public function convertToProject(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('update', $opportunity);

        if ($opportunity->won_delivery_project_id) {
            return response()->json([
                'message' => 'تم تحويل هذه الفرصة مسبقاً لمشروع / Already converted to a project.',
            ], 422);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:projects,code'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project = DB::transaction(function () use ($opportunity, $validated, $request) {
            $nameAr = $validated['name_ar'] ?: 'مشروع تسليم: ' . $opportunity->title_ar;

            $project = Project::create([
                'code' => $validated['code'],
                'name_ar' => $nameAr,
                'name_en' => $opportunity->title_en ? 'Delivery: ' . $opportunity->title_en : null,
                'description_ar' => "مشروع تسليم تم إنشاؤه تلقائياً من الصفقة الرابحة: {$opportunity->title_ar}\nالعميل: {$opportunity->client->name_ar}",
                'type' => 'client_delivery',
                'status' => 'active',
                'priority' => 'high',
                'lead_id' => $request->user()->id,
                'product_id' => $opportunity->product_id,
                'client_id' => $opportunity->client_id,
                'start_date' => now(),
                'due_date' => $validated['due_date'] ?? now()->addDays(30),
            ]);

            // Add standard delivery milestones
            $project->milestones()->createMany([
                ['title_ar' => '1. إعداد البيئة السحابية وتثبيت النظام', 'due_date' => now()->addDays(5)],
                ['title_ar' => '2. تخصيص الهوية والشعار والبيانات الأولية', 'due_date' => now()->addDays(12)],
                ['title_ar' => '3. تدريب فريق العميل والتسليم النهائي', 'due_date' => now()->addDays(25)],
            ]);

            // Attach creator as lead
            $project->members()->attach($request->user()->id, ['role' => 'lead']);

            // Update opportunity
            $opportunity->update([
                'stage' => 'won',
                'won_delivery_project_id' => $project->id,
            ]);

            AuditLog::log('opportunity.converted_to_project', $opportunity, [
                'project_id' => $project->id,
                'project_code' => $project->code,
            ]);

            return $project;
        });

        $project->load(['client', 'product', 'lead', 'milestones']);

        return response()->json([
            'message' => 'تم إنشاء مشروع التسليم بنجاح من الفرصة الرابحة! 🚀',
            'project' => new ProjectResource($project),
        ], 201);
    }
}
