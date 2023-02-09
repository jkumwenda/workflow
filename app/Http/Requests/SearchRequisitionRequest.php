<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequisitionRequest extends FormRequest
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

    public function validationData()
    {
        $newData = [];
        foreach (['created', 'updated'] as $fieldName) {
            if ($this->filled($fieldName)) {
                $value = $this->input($fieldName);
                $range = explode('-', $value);
                $newData[$fieldName]['start'] = trim($range[0]);
                $newData[$fieldName]['end'] = count($range) == 1 ? trim($range[0]) : trim($range[1]);
            }
        }

        //Update request values
        $this->merge($newData);

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'q'     => [
                'nullable',
                Rule::in(['all','confirmed','delegating','mine','archived']),
            ],
            'type'          => 'nullable|in:procurements,travels,transports,subsistences,purchases,orders,vouchers',
            'order'         => 'nullable|max:20',
            'dir'           => 'nullable|in:asc,desc',
            'id'            => 'nullable|max:12',
            'procurement_id' => 'nullable|max:12',
            'purchase_id'   => 'nullable|max:12',
            'title'         => 'nullable|max:50',
            'owner'         => 'nullable|max:50',
            'unit'          => 'nullable|max:50',
            'unitCategory'  => 'nullable|in:MAIN,PROJECT',
            'status'        => 'nullable|max:40', /*exists:requisition_statuses,id,*/
            'created'       => 'nullable',
            'created.start' => 'date',
            'created.end'   => 'date',
            'updated'       => 'nullable',
            'updated.start' => 'date',
            'updated.end'   => 'date',
            'current'       => 'nullable|max:50',
            'route'         => 'nullable|max:10',
            'supplier'      => 'nullable|max:50',
        ];
    }
}
