<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
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
            'name' =>'required',
            'surname' =>'required',
            'patronymic' =>'required',
            'birthday' =>'required',
            'med_exam' =>'required',
            'town' =>'required',
            'street' =>'required',
            'home_num' =>'required',
            'week_end' =>'required',
        ];
        switch ($this->getMethod())
        {
            case 'POST':
                return [
                        'password' =>'required|min:6|max:40|confirmed ',
                        'phone' =>'required|unique:users',
                ]+ $rules;
            case 'PUT':
                return [
                        'phone' =>'required',
                    ]+ $rules;
            case 'DELETE':
                return [
                ];
        }
    }

    public function messages()
    {
        return [
            'phone.required' => 'Телефон является обязательным для заполнения',
            'phone.unique' => 'Пользователь с данным Телефоном уже зарегистрирован',
            'name.required' => 'Имя является обязательным для заполнения',
            'password.required'  => 'Пароль является обязательным для заполнения',
            'password.confirmed'  => 'Пароли не совподяют',
            'password.min'  => 'Пароль слишком лёгкий',
            'password.max'  => 'Пароль слишком длинный',
            'surname.required'  => 'Пароль является обязательным для заполнения',
            'patronymic.required'  => 'Пароль является обязательным для заполнения',
        ];


    }
}
