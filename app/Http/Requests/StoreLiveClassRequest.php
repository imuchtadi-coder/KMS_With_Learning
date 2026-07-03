<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLiveClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'provider' => ['required', 'in:jitsi,google_meet,zoom,other'],
            'meeting_url' => ['nullable', 'url'],
            'meeting_id' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'recording_url' => ['nullable', 'url'],
            'recurring' => ['boolean'],
        ];
    }
}
