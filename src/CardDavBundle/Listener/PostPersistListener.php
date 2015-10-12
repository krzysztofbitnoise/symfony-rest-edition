<?php
namespace CardDavBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use CardDavBundle\Entity\Contact;

class PostPersistListener
{
    /**
     * If post persisted entity is Contact then set uri for it
     * @param LifecycleEventArgs $event
     * @return void
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Contact) {
            $entity->setUri();
            $em = $event->getEntityManager();
            $em->persist($entity);
            $em->flush();
        }
    }
}
