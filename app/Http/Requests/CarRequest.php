<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarRequest extends FormRequest
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
        $rules = [
            'color_id' =>'required',
            'model_id' =>'required',
            'end_of_inspection' =>'required',
            'end_of_insurance' =>'required',
            'number' =>'required',
            'count_places' =>'required',
        ];
        return $rules;
//        switch ($this->getMethod())
//        {
//            case 'POST':
//                return $rules;
//            case 'PUT':
//                return $rules;
//            case 'DELETE':
//                return [
//                ];
//        }
    }

    public function messages()
    {
        return [

        ];


    }
}
