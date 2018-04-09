<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Controller;

use App\Model\UserLogin;
use GuzzleHttp\Psr7\ServerRequest;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserController extends AbstractController
{
    /**
     * @Inject("users")
     * @var array[username => md5(pass)]
     */
    private $users;

    public function loginAction(RequestInterface $request)
    {
        if ($_SESSION['user']) {
            header("Location: /");
            exit;
        }

        parse_str($request->getBody()->getContents(), $data);
        $model = new UserLogin($data);

        if (!$model->validate()) {
            $this->fm->add(implode('<br>', $model->getErrors()), FlashMessages::ERROR);
            header("Location: /");
            exit;
        }

        if (
            !isset($this->users[$model->getUsername()])
            || md5($model->getPassword()) !== $this->users[$model->getUsername()]
        ) {
            $this->fm->add("Username or password are not valid", FlashMessages::ERROR);
            header("Location: /");
            exit;
        }

        $_SESSION["user"] = $model->getUsername();

        $this->fm->add('You are Welcome!', FlashMessages::SUCCESS, '/');
    }

    public function logoutAction(RequestInterface $request)
    {
        session_unset();
        session_destroy();
        header("Location: /");
        exit;
    }
}
