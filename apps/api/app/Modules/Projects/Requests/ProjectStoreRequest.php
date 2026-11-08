<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('projects')->ignore($projectId)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['internal_product', 'client_delivery', 'operational'])],
            'status' => ['required', Rule::in(['planning', 'active', 'on_hold', 'completed', 'archived'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'lead_id' => ['nullable', 'exists:users,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['exists:users,id'],
        ];
    }
}
