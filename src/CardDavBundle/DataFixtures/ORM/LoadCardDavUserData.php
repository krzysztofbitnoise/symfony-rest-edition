<?php
namespace Mgb\EmergencyCommunicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CardDavBundle\Entity\CardDavUser;

class LoadCardDavUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userMod = new CardDavUser();
        $userMod->setUsername('mod');
        $userMod->setPassword('1234');
        $manager->persist($userMod);
        $manager->flush();

        $this->addReference('carddav-user-mod', $userMod);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}