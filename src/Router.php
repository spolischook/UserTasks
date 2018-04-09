<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct($baseUrl = '/')
    {
        $this->routeCollection = new RouteCollection();
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws NoConfigurationException  If no routing configuration could be found
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    public function matchRequest(RequestInterface $request): array
    {
        $context = new RequestContext(
            $this->baseUrl,
            $request->getMethod()
        );

        $matcher = new UrlMatcher($this->routeCollection, $context);
        $parameters = $matcher->match($request->getUri()->getPath());

        return $parameters;
    }

    public function add(string $routeName, Route $route): self
    {
        $this->routeCollection->add($routeName, $route);

        return $this;
    }
}
