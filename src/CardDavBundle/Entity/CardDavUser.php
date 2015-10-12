<?php

namespace CardDavBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Contact
 * @ORM\Table()
 * @ORM\Entity
 */
class CardDavUser implements UserInterface {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=80)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="CardDavList", mappedBy="cardDavUsers")
     * @var ArrayCollection
     */
    protected $cardDavLists;


    function __construct()
    {
        $this->cardDavLists = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getSalt() { return ""; }

    public function eraseCredentials()
    {

    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = \password_hash($password, PASSWORD_BCRYPT);

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return \password_verify($password, $this->password);
    }

    /**
     * @param CardDavList $list
     * @return $this
     */
    public function addCardDavList(CardDavList $list)
    {
        $this->cardDavLists[] = $list;
        // if(!$this->cardDavLists->contains($list))

        return $this;
    }

    /**
     * @param CardDavList $list
     */
    public function removeCardDavList(CardDavList $list)
    {
        $this->cardDavLists->removeElement($list);
    }

    /**
     * @return ArrayCollection
     */
    public function getCardDavLists()
    {
        return $this->cardDavLists;
    }

    public function getRoles()
    {
        return array('ROLE_CARDDAV_CLIENT');
    }
}