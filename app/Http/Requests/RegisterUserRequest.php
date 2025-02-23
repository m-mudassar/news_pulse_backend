<?php

namespace App\Http\Requests;

class RegisterUserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
        ];
    }
}
