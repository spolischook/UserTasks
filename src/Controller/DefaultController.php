<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Controller;

use App\Model\Task;
use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DefaultController extends AbstractController
{
    const LIST_LIMIT = 3;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function homeAction(RequestInterface $request): ResponseInterface
    {
        parse_str($request->getUri()->getQuery(), $urlParams);

        $page = 1;
        if (isset($urlParams['page']) && $urlParams['page'] > 1) {
            $page = (int) $urlParams['page'];
        }

        $orderBy = [];
        if (isset($urlParams['orderBy'])) {
            $orderBy = $urlParams['orderBy'];
            $orientation = isset($urlParams['orientation']) ? $urlParams['orientation'] : 'ASC';
            $orderBy = [$orderBy => $orientation];
        }

        return new Response(
            200,
            [],
            $this->twig->render(
                'index.html.twig',
                [
                    'tasks' => Task::findBy(
                        [],
                        $orderBy,
                        self::LIST_LIMIT,
                        self::LIST_LIMIT * ($page - 1)
                    ),
                    // todo: Move url params to Twig extension
                    'urlParams' => $urlParams,
                    'pageCount' => (int) ceil(Task::count()/self::LIST_LIMIT),
                ]
            )
        );
    }
}
