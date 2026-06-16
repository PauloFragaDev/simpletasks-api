<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority'    => ['sometimes', Rule::enum(TaskPriority::class)],
            'due_date'    => ['nullable', 'date'],
        ];
    }
}
