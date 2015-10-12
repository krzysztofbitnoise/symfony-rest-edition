<?php
namespace Mgb\EmergencyCommunicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CardDavBundle\Entity\CardDavList;

class LoadCardDavListData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $list = new CardDavList();
        $list->setName('List of example contacts');
        $list->addCardDavUser($this->getReference('carddav-user-mod'));
        $manager->persist($list);
        $manager->flush();

        $this->addReference('carddav-list', $list);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}