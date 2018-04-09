<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Tests\Model;

use App\Model\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /**
     * @dataProvider validateProvider
     * @param array $data
     * @param array $errors
     */
    public function testValidate(array $data, array $errors)
    {
        $model = new Task($data);
        $model->validate();
        $this->assertEquals($errors, $model->getErrors());
    }

    public function validateProvider(): array
    {
        return [
            'valid' => [
                'data' => [
                    'username' => 'test',
                    'email' => 'test@example.com',
                    'text' => 'Very simple example of text',
                    'image' => '/tmp/4Fsk8ffKfs.jpg',
                ],
                'errors' => [
                    'Image is broken, provide another one'
                ],
            ],
            'empty task' => [
                'data' => [],
                'errors' => [
                    'Username is empty',
                    'Email is invalid',
                    'Text length should be between 10 and 200 chapter length',
                    'Image should be provided',
                ],
            ],
        ];
    }
}
