<?php


if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Data class for Tips Subscriptions
 *
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 *
 */
class Subscribe extends Managed_DataObject
{
    const POSITIVE = 'http://activitystrea.ms/schema/1.0/sub-yes';
    const POSSIBLE = 'http://activitystrea.ms/schema/1.0/sub-maybe';
    const NEGATIVE = 'http://activitystrea.ms/schema/1.0/sub-no';

    public $__table = 'tips_subscribe'; // table name
    public $id;                // varchar(36) UUID
    public $uri;               // varchar(255)
    public $profile_id;        // int
    public $tip_id;          // varchar(36) UUID
    public $response;            // tinyint
    public $created;           // datetime

    /**
     * Get an instance by key
     *
     * @param string $k Key to use to lookup (usually 'id' for this class)
     * @param mixed  $v Value to lookup
     *
     * @return RSVP object found, or null for no hits
     */
    function staticGet($k, $v=null)
    {
        return Memcached_DataObject::staticGet('Subscribe', $k, $v);
    }

    /**
     * Get an instance by compound key
     *
     * @param array $kv array of key-value mappings
     *
     * @return Bookmark object found, or null for no hits
     */

    function pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGet('Subscribe', $kv);
    }

    /**
     * Add the compound profile_id/tip_id index to our cache keys
     * since the DB_DataObject stuff doesn't understand compound keys
     * except for the primary.
     *
     * @return array
     */
    function _allCacheKeys() {
        $keys = parent::_allCacheKeys();
        $keys[] = self::multicacheKey('Subscribe', array('profile_id' => $this->profile_id,
                                                    'tip_id' => $this->tip_id));
        return $keys;
    }

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Plan use this tip',
            'fields' => array(
                'id' => array('type' => 'char',
                              'length' => 36,
                              'not null' => true,
                              'description' => 'UUID'),
                'uri' => array('type' => 'varchar',
                               'length' => 255,
                               'not null' => true),
                'profile_id' => array('type' => 'int'),
                'tip_id' => array('type' => 'char',
                              'length' => 36,
                              'not null' => true,
                              'description' => 'UUID'),
                'response' => array('type' => 'char',
                                  'length' => '1',
                                  'description' => 'Y, N, or ? for three-state yes, no, maybe'),
                'created' => array('type' => 'datetime',
                                   'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'subscribe_uri_key' => array('uri'),
                'subscribe_profile_tip_key' => array('profile_id', 'tip_id'),
            ),
            'foreign keys' => array('subscribe_tip_id_key' => array('tip', array('tip_id' => 'id')),
                                    'subscribe_profile_id__key' => array('profile', array('profile_id' => 'id'))),
            'indexes' => array('subscribe_created_idx' => array('created')),
        );
    }

    function saveNew($profile, $tip, $verb, $options=array())
    {
        if (array_key_exists('uri', $options)) {
            $other = Subscribe::staticGet('uri', $options['uri']);
            if (!empty($other)) {
                // TRANS: Client exception thrown when trying to save an already existing Tip ("please respond").
                throw new ClientException(_m('Tip already exists.'));
            }
        }

        $other = Subscribe::pkeyGet(array('profile_id' => $profile->id,
                                     'tip_id' => $tip->id));

        if (!empty($other)) {
            // TRANS: Client exception thrown when trying to save an already existing Tip ("please respond").
            throw new ClientException(_m('Tip already exists.'));
        }

        $subscribe = new Subscribe();

        $subscribe->id          = UUID::gen();
        $subscribe->profile_id  = $profile->id;
        $subscribe->tip_id    = $tip->id;
        $subscribe->response      = self::codeFor($verb);

        if (array_key_exists('created', $options)) {
            $subscribe->created = $options['created'];
        } else {
            $subscribe->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $subscribe->uri = $options['uri'];
        } else {
            $subscribe->uri = common_local_url('showsubscribe',
                                        array('id' => $subscribe->id));
        }

        $subscribe->insert();

        self::blow('subscribe:for-tip:%s', $tip->id);

        // XXX: come up with something sexier

        $content = $subscribe->asString();

        $rendered = $subscribe->asHTML();

        $options = array_merge(array('object_type' => $verb),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $subscribe->uri;
        }

        $tipNotice = $tip->getNotice();

        if (!empty($tipNotice)) {
            $options['reply_to'] = $tipNotice->id;
        }

        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }

    function codeFor($verb)
    {
        switch ($verb) {
        case Subscribe::POSITIVE:
            return 'Y';
            break;
        case Subscribe::NEGATIVE:
            return 'N';
            break;
        case Subscribe::POSSIBLE:
            return '?';
            break;
        default:
            // TRANS: Exception thrown when requesting an undefined verb for Subscribe.
            throw new Exception(sprintf(_m('Unknown verb "%s".'),$verb));
        }
    }

    static function verbFor($code)
    {
        switch ($code) {
        case 'Y':
            return Subscribe::POSITIVE;
            break;
        case 'N':
            return Subscribe::NEGATIVE;
            break;
        case '?':
            return Subscribe::POSSIBLE;
            break;
        default:
            // TRANS: Exception thrown when requesting an undefined code for Subscribe.
            throw new Exception(sprintf(_m('Unknown code "%s".'),$code));
        }
    }

    function getNotice()
    {
        $notice = Notice::staticGet('uri', $this->uri);
        if (empty($notice)) {
            // TRANS: Server exception thrown when requesting a non-exsting notice for an Subscribe ("please respond").
            // TRANS: %s is the RSVP with the missing notice.
            throw new ServerException(sprintf(_m('Subscribe %s does not correspond to a notice in the database.'),$this->id));
        }
        return $notice;
    }

    static function fromNotice($notice)
    {
        return Subscribe::staticGet('uri', $notice->uri);
    }

    static function forTip($tip)
    {
        $keypart = sprintf('Subscribe :for-tip:%s', $tip->id);

        $idstr = self::cacheGet($keypart);

        if ($idstr !== false) {
            $ids = explode(',', $idstr);
        } else {
            $ids = array();

            $subscribe = new Subscribe();

            $subscribe->selectAdd();
            $subscribe->selectAdd('id');

            $subscribe->tip_id = $tip->id;

            if ($subscribe->find()) {
                while ($subscribe->fetch()) {
                    $ids[] = $subscribe->id;
                }
            }
            self::cacheSet($keypart, implode(',', $ids));
        }

        $subscribes = array(Subscribe::POSITIVE => array(),
                       Subscribe::NEGATIVE => array(),
                       Subscribe::POSSIBLE => array());

        foreach ($ids as $id) {
            $subscribe = Subscribe::staticGet('id', $id);
            if (!empty($subscribe)) {
                $verb = self::verbFor($subscribe->response);
                $subscribes[$verb][] = $subscribe;
            }
        }

        return $subscribes;
    }

    function getProfile()
    {
        $profile = Profile::staticGet('id', $this->profile_id);
        if (empty($profile)) {
            // TRANS: Exception thrown when requesting a non-existing profile.
            // TRANS: %s is the ID of the non-existing profile.
            throw new Exception(sprintf(_m('No profile with ID %s.'),$this->profile_id));
        }
        return $profile;
    }

    function getTip()
    {
        $tip = Tips::staticGet('id', $this->tip_id);
        if (empty($tip)) {
            // TRANS: Exception thrown when requesting a non-existing tip.
            // TRANS: %s is the ID of the non-existing tip.
            throw new Exception(sprintf(_m('No tip with ID %s.'),$this->tip_id));
        }
        return $tip;
    }

    function asHTML()
    {
        $tip = Tips::staticGet('id', $this->tip_id);

        return self::toHTML($this->getProfile(),
                            $tip,
                            $this->response);
    }

    function asString()
    {
        $tip = Tips::staticGet('id', $this->tip_id);

        return self::toString($this->getProfile(),
                              $tip,
                              $this->response);
    }

    static function toHTML($profile, $tip, $response)
    {
        $fmt = null;

        switch ($response) {
        case 'Y':
            // TRANS: HTML version of an Subscribe ("please respond") status for a user.
            // TRANS: %1$s is a profile URL, %2$s a profile name,
            // TRANS: %3$s is an tip URL, %4$s an tip title.
            $fmt = _m("<span class='automatic event-rsvp'><a href='%1\$s'>%2\$s</a> has used the tip : <a href='%3\$s'>%4\$s</a>.</span>");
            break;
        case 'N':
            // TRANS: HTML version of an Subscribe ("please respond") status for a user.
            // TRANS: %1$s is a profile URL, %2$s a profile name,
            // TRANS: %3$s is an tip URL, %4$s an tip title.
            $fmt = _m("<span class='automatic event-rsvp'><a href='%1\$s'>%2\$s</a> didnot like the tip : <a href='%3\$s'>%4\$s</a>.</span>");
            break;
        case '?':
            // TRANS: HTML version of an Subscribe ("please respond") status for a user.
            // TRANS: %1$s is a profile URL, %2$s a profile name,
            // TRANS: %3$s is an tip URL, %4$s an tip title.
            $fmt = _m("<span class='automatic event-rsvp'><a href='%1\$s'>%2\$s</a> may use the tip : <a href='%3\$s'>%4\$s</a>.</span>");
            break;
        default:
            // TRANS: Exception thrown when requesting a user's Subscribe status for a non-existing response code.
            // TRANS: %s is the non-existing response code.
            throw new Exception(sprintf(_m('Unknown response code %s.'),$response));
            break;
        }

        if (empty($tip)) {
            $tipUrl = '#';
            // TRANS: Used as tip title when not tip title is available.
            // TRANS: Used as: Username [has used| didnot like ] an unknown tip.
            //$tipTitle = _m('an unknown tip');
        } else {
            $notice = $tip->getNotice();
            $tipUrl = $notice->bestUrl();
//            //$tipTitle = $tip->title;
        }

        return sprintf($fmt,
                       htmlspecialchars($profile->profileurl),
                       htmlspecialchars($profile->getBestName()),
                       htmlspecialchars($tipUrl));
    }

    static function toString($profile, $tip, $response)
    {
        $fmt = null;

        switch ($response) {
        case 'Y':
            // TRANS: Plain text version of an Tip ("please respond") status for a user.
            // TRANS: %1$s is a profile name, %2$s is an event title.
            $fmt = _m('%1$s is attending %2$s.');
            break;
        case 'N':
            // TRANS: Plain text version of an Tip ("please respond") status for a user.
            // TRANS: %1$s is a profile name, %2$s is an event title.
            $fmt = _m('%1$s is not attending %2$s.');
            break;
        case '?':
            // TRANS: Plain text version of an Tip ("please respond") status for a user.
            // TRANS: %1$s is a profile name, %2$s is an event title.
            $fmt = _m('%1$s might attend %2$s.');
            break;
        default:
            // TRANS: Exception thrown when requesting a user's Tip status for a non-existing response code.
            // TRANS: %s is the non-existing response code.
            throw new Exception(sprintf(_m('Unknown response code %s.'),$response));
            break;
        }

        if (empty($tip)) {
            // TRANS: Used as tip title when not tip title is available.
          
            //$tipTitle = _m('an unknown tip');
        } else {
            $notice = $tip->getNotice();
            //$tipTitle = $tip->title;
        }

        return sprintf($fmt,
                       $profile->getBestName());
    }

    function delete()
    {
        self::blow('subscribe:for-tip:%s', $tip->id);
        parent::delete();
    }
}
