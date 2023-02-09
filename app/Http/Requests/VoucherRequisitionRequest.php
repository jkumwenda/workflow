<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'expenditure_code' => 'required|string',
            'excepted_tax' => 'nullable|numeric',
            'withholding_tax_code' => 'nullable|string',
            'tax_applied' => 'nullable|integer|min:0,max:100',
        ];
    }
}
