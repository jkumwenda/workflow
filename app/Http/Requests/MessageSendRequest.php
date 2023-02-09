<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageSendRequest extends FormRequest
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
        $messageableIdRule = 'required_with:messageable_type';
        if ($this->has('messageable_type')) {
            $messageableIdRule .= '|exists:' . $this->messageable_type . ',id';
        }

        return [
            'messageable_type' => 'required_with:messageable_id',
            'messageable_id' => $messageableIdRule,
            'receiver' => 'required|exists:users,id',
            'question' => 'required',
        ];
    }
}
