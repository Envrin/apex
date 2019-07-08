<?php
declare(strict_types = 1);

namespace apex\app\interfaces;


interface AuthInterface {

public function check_login(bool $require_login = false);

public function login();

public function auto_login(int $userid);

public function logout();

public function check_password(string$username, string$password);

public function authenticate_2fa_email(int $is_login = 0);

public function authenticate_2fa_sms();

public function get_encpass();

public function recaptcha();

}

