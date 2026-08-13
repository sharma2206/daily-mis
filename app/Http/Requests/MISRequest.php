<?php

namespace App\Http\Requests;

use App\Enums\Branch;
use Illuminate\Foundation\Http\FormRequest;

class MISRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'branch' => $this->route('branch'),
            'date' => $this->route('date'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $branches = implode(',', array_map(fn($case) => $case->value, Branch::cases()));

        return [
            'branch' => "required|string|in:{$branches}",
            'date'   => 'required|date_format:Y-m-d|before_or_equal:today',
        ];
    }

    /**
     * Get the branch enum instance.
     *
     * @return Branch
     */
    public function branch(): Branch
    {
        return Branch::from($this->validated('branch'));
    }

    /**
     * Get the date string.
     *
     * @return string
     */
    public function reportDate(): string
    {
        return $this->validated('date');
    }
}
