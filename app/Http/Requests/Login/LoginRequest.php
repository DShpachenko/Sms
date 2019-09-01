<?php

namespace App\Http\Requests\Login;

use App\Models\User;
use App\Models\UserLogin;
use App\Http\Requests\Validation;

/**
 * Валидация входящего запроса для авторизации.
 *
 * Class LoginRequest
 * @package App\Http\Requests
 */
class LoginRequest extends Validation
{
    /**
     * Метод валидации регистрационных данных.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function make($request): bool
    {
        $data = $request->all();

        if (isset($data['phone']) && $data['phone'] !== '') {
            $data['phone'] = User::clearPhoneNumber($data['phone']);
        }

        $this->setRules([
            'phone' => 'required|min:5|max:30',
            'password' => 'required|min:6|max:50',
        ]);

        $this->setMessages([
            'phone.required' => __('response.phone_required'),
            'password.required' => __('response.password_required'),
            'min' => __('response.min'),
            'max' => __('response.max'),
        ]);

        $this->validateForm($data);

        /** @todo убрать это условие после интеграции api с клиентов */
        if (env('APP_ENV') === 'production') {
            $this->validateIp($request->ip(), UserLogin::TYPE_LOGIN);
        }

        $this->validateUserByPhone($request->get('phone'), User::STATUS_VERIFIED);

        return $this->fails();
    }
}
