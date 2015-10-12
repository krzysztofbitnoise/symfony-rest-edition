<?php
/**
 * Created by Syonix GmbH.
 * User: info@syonix.ch
 *
 */
namespace CardDavBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\MaxDepth;


/**
 * Class Contact
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Contact {

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
     * @ORM\Column(name="firstName", type="string", length=80)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=80)
     */
    private $lastName;

    /**
     * Gender id follows ISO/IEC 5218 standard.
     * See http://en.wikipedia.org/wiki/ISO_5218 for additional information.
     *
     * @ORM\Column(name="gender", type="integer")
     */
    private $gender;

    /**
     * @ORM\Column(name="mobilePhone", type="string", length=30)
     */
    private $mobilePhone;

    /**
     * @ORM\Column(name="phone", type="string", length=30)
     */
    private $phone;

    /**
     * @ORM\Column(name="email", type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(name="address", type="string", length=80)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="etag", type="string", length=100, nullable=true)
     */
    private $etag;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="string", length=100, nullable=true)
     */
    private $uri;

    /**
     * @ORM\ManyToOne(targetEntity="CardDavList", inversedBy="contacts")
     * @var ArrayCollection
     */
    protected $list;

    function __construct()
    {
        $this->etag = md5(microtime());
    }

    function __toString()
    {
        return $this->firstName . " " . $this->lastName;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return strtolower($this->email);
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getName() {
        return $this->firstName." ".$this->lastName;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;
    }

    /**
     * @return mixed
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
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
     * @param string $etag
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
    }

    /**
     * @return string
     */
    public function getEtag()
    {
        return $this->etag;
    }


    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Contact
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * 
     */
    public function setUri()
    {
        $this->uri = 'contact-' . $this->getId() . '.vcf';
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri ? $this->uri : 'contact-' . $this->getId() . '.vcf';
    }

    /**
     * Set list
     *
     * @param \CardDavBundle\Entity\CardDavList $list
     *
     * @return Contact
     */
    public function setList(\CardDavBundle\Entity\CardDavList $list = null)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return \CardDavBundle\Entity\CardDavList
     */
    public function getList()
    {
        return $this->list;
    }
}
