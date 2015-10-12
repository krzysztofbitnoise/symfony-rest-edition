<?php

namespace CardDavBundle\CardDAV\Backend;

use Sabre\CardDAV;
use Sabre\DAV;
use Sabre\CardDAV\Backend\SyncSupport;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\VObject\Component\VCard;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\HTTP\URLUtil;
use CardDavBundle\Entity\Contact;

/**
 * CardDAV backend
 *
 * This CardDAV backend uses EntityManager to store addressbooks (CardDavList entity)
 *
 */
class CardDavList extends AbstractBackend implements SyncSupport {

    /**
     * EntityManager
     */
    protected $em;

    /**
     * Sets up the object
     *
     * @param EntityManagerInterface $em
     */
    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns the list of addressbooks for a specific user.
     *
     * @param string $principalUri
     * @return array
     */
    public function getAddressBooksForUser($principalUri)
    {
        $splitedPath  = URLUtil::splitPath($principalUri);
        $user         = $this->em->getRepository('CardDavBundle:CardDavUser')->findOneByUsername($splitedPath[1]);
        $addressBooks = [];
        if ($user) {
            $userLists = $user->getCardDavLists();

            foreach($userLists as $list) {
                if ($list->isActive()) {
                    $addressBooks[] = [
                        'id'                                                          => $list->getId(),
                        'uri'                                                         => $list->getSlug(),
                        'principaluri'                                                => $principalUri,
                        '{DAV:}displayname'                                           => $list->getName(),
                        '{' . CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $list->getName(),
                        '{http://calendarserver.org/ns/}getctag'                      => $list->getSynctoken(),
                        '{http://sabredav.org/ns}sync-token'                          => $list->getSynctoken(),
                    ];
                }

            }
        }

        return $addressBooks;
    }


    /**
     * Disabled.
     * Updates properties for an address book.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param string $addressBookId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
    public function updateAddressBook($addressBookId, \Sabre\DAV\PropPatch $propPatch)
    {
        $this->addChange($addressBookId, "", 2);

        return true;
    }

    /**
     * Disabled - Creates a new address book
     *
     * @param string $principalUri
     * @param string $url Just the 'basename' of the url.
     * @param array $properties
     * @return integer 0
     */
    public function createAddressBook($principalUri, $url, array $properties)
    {
        return 0;
    }

    /**
     * Disabled - Deletes an entire addressbook and all its contents
     *
     * @param int $addressBookId
     * @return void
     */
    public function deleteAddressBook($addressBookId)
    {

    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     * @return array
     */
    public function getCards($addressbookId)
    {
        $list     = $this->em->getRepository('CardDavBundle:CardDavList')->findOneById($addressbookId);
        $contacts = $this->em->getRepository('CardDavBundle:Contact')->findBy(['list' => $list]);
        $result   = [];
        foreach ($contacts as $key => $contact) {
            $result[] = $this->generateVCard($contact);     
        }

        return $result;
    }

    /**
     * Returns a specfic card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * If the card does not exist, you must return false.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        $contact  = $this->em->getRepository('CardDavBundle:Contact')->findOneByUri($cardUri);
            
        return $contact ? $this->generateVCard($contact) : false;
    }

    /**
     * Returns a list of cards.
     *
     * This method should work identical to getCard, but instead return all the
     * cards in the list as an array.
     *
     * If the backend supports this, it may allow for some speed-ups.
     *
     * @param mixed $addressBookId
     * @param array $uris
     * @return array
     */
    public function getMultipleCards($addressBookId, array $uris)
    {
        return array_map(function($uri) use ($addressBookId) {

            return $this->getCard($addressBookId, $uri);
        }, $uris);
    }

    /**
     * Disabled - Creates a new card. It returns md5 always and updates addressBook synctoken
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string
     */
    public function createCard($addressBookId, $cardUri, $cardData)
    {
        $this->addChange($addressBookId, $cardUri, 1);

        return '"' . md5(time()) . '"';
    }

    /**
     * Modyfied - Updates a card. It updates ETag of card only.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressBooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData)
    {
        $contact  = $this->em->getRepository('CardDavBundle:Contact')->findOneBy(['uri' => $cardUri]);
        $etag     = null;
        if ($contact) {
            //md should be created only from vcard data. but clients can't update it, that is why we add uniqid()
            $etag = md5($contact->getUri() . uniqid()); 
            $contact->setEtag($etag);
            $this->em->persist($contact);
            $this->em->flush();

            $this->addChange($addressBookId, $cardUri, 2);
        }

        return is_string($etag) ? '"' . $etag . '"' : $etag;
    }

    /**
     * Deletes a card
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri)
    {
        $this->updateCard($addressBookId, $cardUri, 'cardData does not metter');

        return false;
    }

    /**
     * Disabled.
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified address book.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'updated.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the addressbook, as reported in the {http://sabredav.org/ns}sync-token
     * property. This is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param string $addressBookId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return null
     */
    public function getChangesForAddressBook($addressBookId, $syncToken, $syncLevel, $limit = null)
    {
        return null;
    }

    /**
     * Updates synctoken property of CardDavList
     *
     * @param mixed $addressBookId
     * @param string $objectUri
     * @param int $operation 1 = add, 2 = modify, 3 = delete
     * @return void
     */
    protected function addChange($addressBookId, $objectUri, $operation)
    {
        $list = $this->em->getRepository('CardDavBundle:CardDavList')->findOneById($addressBookId);
        if ($list) {
            $list->incrementSyncToken();
            $this->em->flush($list);
        } else {
            throw new DAV\Exception(sprintf('There is no list for id %s', $addressBookId));
        }
    }

    /**
     * Create array with vcard data ( serialized Sabre\VObject\Component\VCard object )
     *
     * @param Contact $contact
     *
     * @return array
     */
    private function generateVCard(Contact $contact)
    {
        //generate proper VCard
        $vcard = new VCard([
            'FN'    => $contact->getName(),
            'TEL'   => $contact->getPhone(),
            'N'     => [$contact->getLastName(), $contact->getFirstName()],
            'EMAIL' => $contact->getEmail(),
            // 'TITLE' => $contact->getJobTitle(),
            'UID'   => $contact->getId()
        ]);
        $vcard->add('TEL', $contact->getMobilePhone(), ['type' => 'CELL']);
        $vcard->add('ADR', $contact->getAddress(), ['type' => 'work']);
        $vcard->add('NOTE', $contact->getComment());

        return $card = [
            'id'           => $contact->getId(),
            'carddata'     => $vcard->serialize(),
            'uri'          => $contact->getUri(),
            'lastmodified' => time(),
            'etag'         => '"' . $contact->getEtag() . '"',
            // 'size' => TODO
        ];
    }
}
