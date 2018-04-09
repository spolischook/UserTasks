<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App;

use App\Model\Task;

class Guard
{
    const EDIT = 'EDIT';
    const CREATE = 'CREATE';
    const DELETE = 'DELETE';

    private $accessRights = [
        Task::class => [
            'admin' => [
                self::CREATE,
                self::EDIT,
                self::DELETE,
            ]
        ]
    ];

    public function getUser()
    {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public function isGranted($action, $subject): bool
    {
        if (!$user = $this->getUser()) {
            return false;
        }

        $class = is_object($subject) ? get_class($subject) : $subject;

        if (!isset($this->accessRights[$class][$user])) {
            return false;
        }

        return in_array(strtoupper($action), $this->accessRights[$class][$user]);
    }
}
