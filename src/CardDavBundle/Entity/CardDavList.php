<?php

namespace CardDavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CardDavBundle\Util\String;

/**
 * Class Contact
 * @ORM\Table()
 * @ORM\Entity
 */
class CardDavList {

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
     * @ORM\Column(name="name", type="string", length=80)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=80)
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="synctoken", type="string", length=100, nullable=true)
     */
    private $syncToken;

    /**
     * @ORM\ManyToMany(targetEntity="CardDavUser", inversedBy="cardDavLists")
     * @var ArrayCollection
     */
    protected $cardDavUsers;

    /**
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="list")
     * @var ArrayCollection
     */
    protected $contacts;


    function __construct()
    {
        $this->tags         = new ArrayCollection();
        $this->cardDavUsers = new ArrayCollection();
        $this->syncToken    = 1;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->slug = String::toAscii($name);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param CardDavUser $user
     * @return $this
     */
    public function addCardDavUser(CardDavUser $user)
    {
        if(!$this->cardDavUsers->contains($user))
            $this->cardDavUsers[] = $user;

        return $this;
    }

    /**
     * @param CardDavUser $user
     */
    public function removeCardDavUser(CardDavUser $user)
    {
        $this->cardDavUsers->removeElement($user);
    }

    /**
     * @return ArrayCollection
     */
    public function getCardDavUsers()
    {
        return $this->cardDavUsers;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function activate()
    {
        $this->active = true;
    }

    public function deactivate()
    {
        $this->active = false;
    }

    /**
     * @param string $synctoken
     */
    public function setSyncToken($syncToken)
    {
        $this->syncToken = $syncToken;
    }

    /**
     * @return string
     */
    public function getSyncToken()
    {
        return $this->syncToken;
    }

    /**
     * @param string $synctoken
     */
    public function incrementSyncToken()
    {
        $this->syncToken++;
    }
}
