<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App;

use App\Controller\DefaultController;
use App\Controller\UserController;
use App\Controller\UserTaskController;
use function DI\autowire;
use DI\ContainerBuilder;
use function DI\factory;
use function DI\get;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Psr7\Response;
use Plasticbrain\FlashMessages\FlashMessages;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;

class Kernel implements RequestHandlerInterface
{
    /**
     * @var Connection
     */
    public static $db;

    public static $kernelDir = __DIR__;

    protected $container;

    protected $router;

    protected $env;

    public function __construct($env)
    {
        $this->router = new Router('');
        $this->env = $env;
    }

    public static function getPublicDir()
    {
        return realpath(__DIR__.'/../public');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $container = $this->createContainer();
            $this->loadRoutes();

            $parameters = $this->router->matchRequest($request);
            $response = $container->get($parameters['_controller'])->{$parameters['_action']}($request, $parameters);
        } catch (NoConfigurationException | ResourceNotFoundException | MethodNotAllowedException $e) {
            $response = new Response(404);
            // todo: log it
            // todo: make a html representation of error page
        } catch (\Exception $e) {
            if ($this->env === 'dev') {
                throw $e;
            }

            $response = new Response(503);
            // todo: log it
            // todo: mail exception to emergency mail list
            // todo: make a html representation of error page
        }

        return $response;
    }

    /**
     * @todo Compile container for production environment
     * @return ContainerInterface
     * @throws \Exception
     */
    public function createContainer(): ContainerInterface
    {
        if ($this->container) {
            return $this->container;
        }

        $currentUrl = null;
        if (isset($_SERVER)) {
            $currentUrl = parse_url($_SERVER['REQUEST_URI'])['path'];
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__.'/../config.php');
        $containerBuilder->addDefinitions([
            Connection::class => factory([DriverManager::class, 'getConnection'])
                ->parameter('params', get('database.connectionParams')),
            \Twig_Loader_Filesystem::class => autowire()
                ->constructor(get('template.path')),
            \Twig_Environment::class => autowire()
                ->constructor(
                    get(\Twig_Loader_Filesystem::class),
                    [
                        'cache' => get('template.cache')
                    ]
                )->method('addGlobal', 'flashMessages', get(FlashMessages::class))
                ->method('addGlobal', 'guard', get(Guard::class))
                ->method('addGlobal', 'currentUrl', $currentUrl),
        ]);

        $containerBuilder->useAnnotations(true);

        $this->container = $containerBuilder->build();

        self::$db = $this->container->get(Connection::class);
//        self::$db->connect();

        return $this->container;
    }

    /**
     * @todo Load routes from configuration
     */
    private function loadRoutes(): void
    {
        $this->router->add('home', new Route(
            '/',
            [
                '_controller' => DefaultController::class,
                '_action' => 'homeAction'
            ],
            [],
            [],
            '',
            [],
            ['GET']
        ));
        $this->router->add('new-task', new Route(
            '/user-tasks/new',
            [
                '_controller' => UserTaskController::class,
                '_action' => 'newAction'
            ],
            [],
            [],
            '',
            [],
            ['GET']
        ));
        $this->router->add('create-task', new Route(
            '/user-tasks/new',
            [
                '_controller' => UserTaskController::class,
                '_action' => 'createAction'
            ],
            [],
            [],
            '',
            [],
            ['POST']
        ));
        $this->router->add('edit-task', new Route(
            '/user-tasks/{id}',
            [
                '_controller' => UserTaskController::class,
                '_action' => 'editAction'
            ],
            [],
            [],
            '',
            [],
            ['POST', 'GET']
        ));
        $this->router->add('login', new Route(
            '/login',
            [
                '_controller' => UserController::class,
                '_action' => 'loginAction'
            ],
            [],
            [],
            '',
            [],
            ['POST']
        ));
        $this->router->add('logout', new Route(
            '/logout',
            [
                '_controller' => UserController::class,
                '_action' => 'logoutAction'
            ],
            [],
            [],
            '',
            [],
            ['POST']
        ));
    }
}
