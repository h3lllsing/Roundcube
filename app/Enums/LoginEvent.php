<?php

namespace App\Enums;

enum LoginEvent: string
{
    case LoginSuccess = 'login_success';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case LoginSuspended = 'login_suspended';
    case PasswordReset = 'password_reset';
}
