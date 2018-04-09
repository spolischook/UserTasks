<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Controller;

use App\Guard;
use App\Model\Task;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserTaskController extends AbstractController
{
    /**
     * @Inject
     * @var Guard
     */
    private $guard;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function newAction(RequestInterface $request): ResponseInterface
    {
        return new Response(
            200,
            [],
            $this->twig->render('create_task.html.twig', [])
        );
    }

    public function createAction(ServerRequest $request)
    {
        $model = new Task(array_merge($request->getParsedBody(), $request->getUploadedFiles()));

        if (!$model->validate()) {
            $this->fm->add(implode('<br>', $model->getErrors()), FlashMessages::ERROR);
            return new Response(
                200,
                [],
                $this->twig->render('create_task.html.twig', ['model' => $model])
            );
        }

        $model->save();

        $this->fm->add('Your task was successfully saved!', FlashMessages::SUCCESS, '/');
    }

    public function editAction(ServerRequest $request, array $parameters)
    {
        $task = Task::find($parameters['id']);

        if (!$task) {
            return new Response(404);
        }

        if (!$this->guard->isGranted('EDIT', $task)) {
            // Yea baby! It's a security! Villain should even don't know that this page exists
            return new Response(404);
        }

        if ('POST' === $request->getMethod()) {
            $body = $request->getParsedBody();
            $task->setStatus((int) isset($body['status']));
            $task->setText($body['text']);

            if (!$task->validate()) {
                $this->fm->add(implode('<br>', $task->getErrors()), FlashMessages::ERROR);
                return new Response(
                    200,
                    [],
                    $this->twig->render('edit_task.html.twig', ['model' => $task])
                );
            }

            $task->save();
            $this->fm->add('Task was successfully edited!', FlashMessages::SUCCESS, '/');
        }

        return new Response(
            200,
            [],
            $this->twig->render('edit_task.html.twig', ['model' => $task])
        );
    }
}
