<?php

namespace Liip\HelloBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Request\ParamFetcherInterface;


class RestController extends Controller
{
    /**
     * Get the list of articles
     *
     * @param ParamFetcher $paramFetcher
     * @param string $page integer with the page number (requires param_fetcher_listener: force)
     * @return array data
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     * @ApiDoc()
     */
    public function getConnectionsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $articles = array('bim', 'bam', 'bingo');

        $data = new HelloResponse($articles, $page);
        $view = new View($data);
        $view->setTemplate('LiipHelloBundle:Rest:getArticles.html.twig');
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
