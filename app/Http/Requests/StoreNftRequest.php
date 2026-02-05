<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:160'],
            'collection_id' => ['required','integer','exists:collections,id'],
            'listing_ref_amount' => ['required','numeric','min:0'],
            'listing_ref_currency' => ['required','string','max:10'],
            'editions_total' => ['required','integer','min:1'],
            'image' => ['required','image','mimes:jpeg,png,webp','max:4096','dimensions:max_width=6000,max_height=6000'],
            //
        ];
    }
}
