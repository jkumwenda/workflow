<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequisitionRequest extends FormRequest
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
            'procurement_id' => 'required|exists:procurements,id',
            'procurement_item_id' => 'required|array|min:1',
            'procurement_item_id.*' => 'exists:procurement_items,id',
            'price' => 'required|array|min:1|check_array:1',
            'price.*' => 'nullable|numeric',
        ];
    }
}
