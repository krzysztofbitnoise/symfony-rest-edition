<?php

namespace CardDavBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use
    Sabre\DAV,
    Sabre\CardDAV,
    Sabre\CalDAV,
    Sabre\DAVACL;

class CardDavController extends Controller
{
    public function indexAction()
    {
        $pdo = $this->container->get('doctrine.dbal.default_connection')->getWrappedConnection();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Uncomment to hide Sf2 error handler
        // Mapping PHP errors to exceptions
        $error_handler = function($errno, $errstr, $errfile, $errline ) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        };
        set_error_handler($error_handler);

        $principalBackend = $this->get('carddav.carddavuser_backend');

        // Directory tree
        // those will be listed at start
        $tree = array(
            new DAVACL\PrincipalCollection($principalBackend),
            new CardDAV\AddressBookRoot($principalBackend, $this->get('carddav.carddavlist_backend'))
        );

        // The object tree needs in turn to be passed to the server class
        $server = new DAV\Server($tree);

        //debug DAV server exceptions
        $server->debugExceptions = $this->container->getParameter('carddav_debug');

        // You are highly encouraged to set your WebDAV server base url. Without it,
        // SabreDAV will guess, but the guess is not always correct. Putting the
        // server on the root of the domain will improve compatibility.
        $server->setBaseUri($this->generateUrl('carddav_server'));

        // CardDAV plugin
        $carddavPlugin = new CardDAV\Plugin();
        $server->addPlugin($carddavPlugin);

        // Auth plugin
        $authPlugin = new DAV\Auth\Plugin($this->get('carddav.basic_auth'), 'CardDAV');
        $server->addPlugin($authPlugin);

        // ACL plugin
        $aclPlugin = new DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        // Support for html frontend
        if ($this->container->getParameter('carddav_browser')) {
            $browser = new DAV\Browser\Plugin();
            $server->addPlugin($browser);
        }

        //custom plugins
        $server->addPlugin($this->get('carddav.plugin.contact'));

        // And off we go!
        $server->exec();

        //sf kernel events will not be fired
        exit();
    }
}
