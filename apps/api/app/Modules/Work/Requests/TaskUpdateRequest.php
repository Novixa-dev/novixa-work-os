<?php

namespace App\Modules\Work\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->is_active ?? false;
    }

    public function rules(): array
    {
        return [
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'milestone_id' => ['nullable', 'exists:project_milestones,id'],
            'parent_id' => ['nullable', 'exists:tasks,id'],
            'status' => ['nullable', Rule::in(['backlog', 'todo', 'in_progress', 'in_review', 'done', 'cancelled'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'kanban_order' => ['nullable', 'integer'],
            'is_my_day' => ['nullable', 'boolean'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['exists:task_labels,id'],
        ];
    }
}
