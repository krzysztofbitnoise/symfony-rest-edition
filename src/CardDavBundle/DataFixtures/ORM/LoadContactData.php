<?php
namespace Mgb\EmergencyCommunicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use CardDavBundle\Entity\Contact;

class LoadContactData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /* Contacts */
        $faker = Factory::create('en_US');
        for($i = 1; $i <= 20; $i++) {
            $faker->seed($i * 12);
            $contact = new Contact();
            $gender = $i%2;
            $contact->setFirstName($gender == 1 ? $faker->firstNameMale : $faker->firstNameFemale);
            $contact->setLastName($faker->lastName);
            $contact->setGender($gender);
            $contact->setMobilePhone($faker->phoneNumber);
            $contact->setPhone($faker->phoneNumber);
            $contact->setEmail($faker->safeEmail);
            $contact->setAddress($faker->streetAddress);
            $contact->setComment($faker->text(200));
            $contact->setList($this->getReference('carddav-list'));
            $manager->persist($contact);
            $this->addReference('contact-'. $i, $contact);
        }
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}