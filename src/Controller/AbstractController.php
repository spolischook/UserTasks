<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Controller;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Uri;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractController
{
    protected $connection;

    protected $twig;

    protected $fm;

    public function __construct(Connection $connection, \Twig_Environment $twig)
    {
        $this->connection = $connection;
        $this->twig = $twig;
        $this->fm = new FlashMessages();
    }

    protected function getUrl(RequestInterface $request): string
    {
        return Uri::composeComponents(
            $request->getUri()->getScheme(),
            $request->getUri()->getAuthority(),
            $request->getUri()->getPath(),
            $request->getUri()->getQuery(),
            $request->getUri()->getFragment()
        );
    }
}
