<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Model;

use Respect\Validation\Validator as v;

class UserLogin extends AbstractModel
{
    protected $username;

    protected $password;

    public function validate(): bool
    {
        $isValid = true;

        if (!v::stringType()->notEmpty()->validate($this->username)) {
            $this->errors[] = 'Username is empty';
            $isValid = false;
        }

        if (!v::stringType()->notEmpty()->validate($this->password)) {
            $this->errors[] = 'Password not provided';
            $isValid = false;
        }

        return $isValid;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
