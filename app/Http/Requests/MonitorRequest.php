<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CheckInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'interval' => ['required', 'integer', Rule::in(CheckInterval::values())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
