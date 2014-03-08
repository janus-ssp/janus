<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyController extends Controller
{
    /**
     * @Route("/legacy/{uri}")
     */
    public function indexAction(Request $request)
    {
        $file = $request->server->get('PATH_INFO');

        $pageControllerPath = JANUS_ROOT_DIR . '/www' . $file;

//echo 'test';
//        I found a better way I guess:
        ob_start();
        if (file_exists($pageControllerPath)) {
            require_once $pageControllerPath;
        } else {
            require_once JANUS_ROOT_DIR . '/www/index.php';
        }
        $content = ob_get_clean();

        return new Response($content);
    }
}