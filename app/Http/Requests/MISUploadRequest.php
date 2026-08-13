<?php

namespace App\Http\Requests;

use App\Enums\Branch;
use Illuminate\Foundation\Http\FormRequest;

class MISUploadRequest extends FormRequest
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
            'branch' => $this->route('branch') ?? $this->input('branch'),
            'date'   => $this->route('date') ?? $this->input('date'),
        ]);

        // Auto-calculate occupancy_pct from occupancy if not provided
        if ($this->filled('occupancy') && $this->filled('branch')) {
            try {
                $branch     = Branch::from($this->input('branch'));
                $occupancy  = (int) $this->input('occupancy');
                $percent    = $branch->bedCount() > 0
                    ? round(($occupancy / $branch->bedCount()) * 100, 2)
                    : 0;
                $this->merge(['occupancy_pct' => $percent]);
            } catch (\ValueError $e) {
                // Invalid branch, let validation handle it
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Chromepet requires 3 files (bill, cashier, package).
     * Oragadam requires 2 files (bill, cashier) + ER count.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $branches = implode(',', array_map(fn($case) => $case->value, Branch::cases()));

        return [
            'branch'       => "required|string|in:{$branches}",
            'date'         => 'required|date_format:Y-m-d|before_or_equal:today',
            'bill_file'    => 'required|file|mimes:csv|max:20480',
            'cashier_file' => 'required|file|mimes:csv|max:20480',
            'package_file' => 'required_if:branch,chromepet|nullable|file|mimes:csv|max:20480',
            'occupancy'    => 'nullable|integer|min:0',
            'occupancy_pct' => 'nullable|numeric|min:0|max:100',
            'admission'    => 'nullable|integer|min:0',
            'discharge'    => 'nullable|integer|min:0',
            'total_op'     => 'nullable|integer|min:0',
            'er_count'     => 'required_if:branch,oragadam|nullable|integer|min:0',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'package_file.required_if' => 'The package consumption file is required for Chromepet branch.',
            'er_count.required_if' => 'The ER count is required for Oragadam branch.',
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
     * Get the validated date string.
     *
     * @return string
     */
    public function reportDate(): string
    {
        return $this->validated('date');
    }

    /**
     * Build the volume data array for MISService.
     *
     * @return array
     */
    public function volumeData(): array
    {
        return [
            'ftd' => [
                'occupancy'     => (int) $this->input('occupancy', 0),
                'occupancy_pct' => (float) $this->input('occupancy_pct', 0),
                'admission'     => (int) $this->input('admission', 0),
                'discharge'     => (int) $this->input('discharge', 0),
                'total_op'      => (int) $this->input('total_op', 0),
                'er_count'      => (int) $this->input('er_count', 0),
            ],
            'mtd' => [
                'occupancy'     => 0,
                'occupancy_pct' => 0,
                'admission'     => 0,
                'discharge'     => 0,
                'total_op'      => 0,
                'er_count'      => 0,
            ],
        ];
    }
}
