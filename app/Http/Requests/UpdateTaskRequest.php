<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:1', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('title')) {
            $payload['title'] = $this->normalizeText($this->input('title'));
        }

        if ($this->has('description')) {
            $payload['description'] = $this->normalizeNullableText($this->input('description'));
        }

        if ($this->has('status')) {
            $payload['status'] = $this->normalizeStatus($this->input('status'));
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    private function normalizeText(mixed $value): string
    {
        $value = is_string($value) ? $value : '';

        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strip_tags($value);
        $value = str_replace("\r\n", "\n", $value);
        $value = preg_replace('/[^\P{C}\n\t]+/u', '', $value) ?? $value;
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeStatus(mixed $value): string
    {
        $value = is_string($value) ? $value : '';

        return strtolower(trim($value));
    }
}
