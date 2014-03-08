<?php
/**
 * Based on: http://symfony.com/doc/current/cookbook/routing/custom_route_loader.html
 */
namespace Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LegacyRoutingLoader implements LoaderInterface
{
    private $loaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "legacy" loader twice');
        }

        $routes = new RouteCollection();


        $wwwIterator = new \DirectoryIterator(JANUS_ROOT_DIR . '/www');
        /** @var \DirectoryIterator $file */
        foreach ($wwwIterator as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            $this->addRoute($routes, $file->getFilename());
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * @param RouteCollection $routes
     */
    private function addRoute(RouteCollection $routes, $name) {
        // prepare a new route
        $pattern = $name;
        $defaults = array(
            '_controller' => 'JanusServiceRegistryCoreBundle:Legacy:index',
        );
        $requirements = array(
//            'parameter' => '\d+',
        );
        $route = new Route($pattern, $defaults, $requirements);

        // add the new route to the route collection:
        $routeName = 'legacy-' . $name;
        $routes->add($routeName, $route);

    }

    public function supports($resource, $type = null)
    {
        return 'legacy' === $type;
    }

    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
