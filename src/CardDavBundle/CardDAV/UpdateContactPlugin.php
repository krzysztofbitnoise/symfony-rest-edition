<?php
namespace CardDavBundle\CardDAV;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use
    Sabre\DAV,
    Sabre\CardDAV\ICard;

/**
 * This plugin update Contact->ETag and CardDavList->synctoken property one more time to force contact re-load from server.
 */
class UpdateContactPlugin extends DAV\ServerPlugin {

    /**
     * EntityManager
     *
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Logger Monolog
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * __construct
     *
     * @param EntityManagerInterface $em
     * @param string $realm
     */
    function __construct(EntityManagerInterface $em, LoggerInterface $logger = null) {

        $this->em     = $em;
        $this->logger = $logger;
    }

    /**
     * Initializes the plugin. This function is automatically called by the server
     *
     * @param DAV\Server $server
     * @return void
     */
    public function initialize(DAV\Server $server) {

        $this->server = $server;
        $this->server->on('afterWriteContent', [$this, 'afterWriteContent'], 101);

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using DAV\Server::getPlugin
     *
     * @return string
     */
    public function getPluginName() {

        return 'updatecontact';

    }

    /**
     * This method is called on afterWriteContent event
     *
     * @param string $uri
     * @param DAV\IFile $node
     * @return bool
     */
    public function afterWriteContent($uri, DAV\IFile $node)
    {
        if ($node instanceof ICard) {
            if ($this->logger) {
                $this->logger->info('before UpdateContactPlugin (eventafterWriteContent) uri: ' . $uri . ' ETag: ' . $node->getETag());
            }
            $node->put('carddata does not matter');
            if ($this->logger) {
                $this->logger->info('after UpdateContactPlugin (event afterWriteContent) uri: ' . $uri . ' ETag: ' . $node->getETag());
            }

            return true;
        }

        return false;
    }

}
