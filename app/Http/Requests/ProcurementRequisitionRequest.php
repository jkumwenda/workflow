<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementRequisitionRequest extends FormRequest
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
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|max:200',
            'item_name' => 'required|array|min:1',
            'item_name.*' => 'required|string|max:100',
            'description' => 'required|array|min:1',
            'description.*' => 'required|string',
            'uom' => 'required|array|min:1',
            'uom.*' => 'required|in:EACH,PACK,BALE,CARTON,CASE,PALLET,REAM,BOTTLE,TUBE,VIALS,AMPULES,METERS,LITERS,BOX',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric',
        ];
    }
}
