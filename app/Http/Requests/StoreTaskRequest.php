<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->normalizeText($this->input('title')),
            'description' => $this->normalizeNullableText($this->input('description')),
            'status' => $this->normalizeStatus($this->input('status')) ?? TaskStatus::Pending->value,
        ]);
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

    private function normalizeStatus(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return strtolower(trim($value));
    }
}
