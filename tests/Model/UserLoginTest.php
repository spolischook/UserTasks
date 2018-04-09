<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Tests\Model;

use App\Model\UserLogin;
use PHPUnit\Framework\TestCase;

class UserLoginTest extends TestCase
{
    /**
     * @dataProvider validateProvider
     */
    public function testValidate($username, $password, bool $isValid, array $errors)
    {
        $model = new UserLogin([
            'username' => $username,
            'password' => $password,
        ]);

        $this->assertEquals($isValid, $model->validate());
        $this->assertEquals($errors, $model->getErrors());
    }

    public function validateProvider()
    {
        return [
            'not valid' => [
                'username' => null,
                'password' => null,
                'isValid' => false,
                'errors' => [
                    'Username is empty',
                    'Password not provided',
                ]
            ],
            'username not valid' => [
                'username' => null,
                'password' => '555',
                'isValid' => false,
                'errors' => [
                    'Username is empty',
                ]
            ],
            'password not valid' => [
                'username' => '555',
                'password' => null,
                'isValid' => false,
                'errors' => [
                    'Password not provided',
                ]
            ],
            'password empty string' => [
                'username' => '555',
                'password' => '',
                'isValid' => false,
                'errors' => [
                    'Password not provided',
                ]
            ],
        ];
    }
}
