<?php


if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Data class for Tips
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 *
 */
class Tips extends Managed_DataObject
{
    const OBJECT_TYPE = 'http://activitystrea.ms/schema/1.0/tips';

    public $__table = 'tips'; // table name
    public $id;                    // varchar(36) UUID
    public $uri;                   // varchar(255)
    public $profile_id;            // int
    public $description;           // text
    public $created;               // datetime

    /**
     * Get an instance by key
     *
     * @param string $k Key to use to lookup (usually 'id' for this class)
     * @param mixed  $v Value to lookup
     *
     * @return Happening object found, or null for no hits
     *
     */
    function staticGet($k, $v=null)
    {
        return Memcached_DataObject::staticGet('Tips', $k, $v);
    }

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'Store tips suggested by users',
            'fields' => array(
                'id' => array('type' => 'char',
                              'length' => 36,
                              'not null' => true,
                              'description' => 'UUID'),
                'uri' => array('type' => 'varchar',
                               'length' => 255,
                               'not null' => true),
                'profile_id' => array('type' => 'int', 'not null' => true),
                'description' => array('type' => 'text'),
                'created' => array('type' => 'datetime',
                                   'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'tips_uri_key' => array('uri'),
            ),
            'foreign keys' => array('tips_profile_id__key' => array('profile', array('profile_id' => 'id'))),
            'indexes' => array('happening_created_idx' => array('created')),
        );
    }

    static function saveNew($profile,$description,$options=array())
    {
        if (array_key_exists('uri', $options)) {
            $other = Tips::staticGet('uri', $options['uri']);
            if (!empty($other)) {
                // TRANS: Client exception thrown when trying to create an event that already exists.
                throw new ClientException(_m('Tip already exists.'));
            }
        }

        $ev = new Tips();

        $ev->id          = UUID::gen();
        $ev->profile_id  = $profile->id;
        $ev->description = $description;

        if (array_key_exists('created', $options)) {
            $ev->created = $options['created'];
        } else {
            $ev->created = common_sql_now();
        }

   if (array_key_exists('uri', $options)) {
            $ev->uri = $options['uri'];
        } else {
            $ev->uri = common_local_url('showtips',
                                        array('id' => $ev->id));
        }
       

        $ev->insert();

        // XXX: does this get truncated?

        // TRANS: Event description. %1$s is a title, %2$s is start time, %3$s is end time,
	// TRANS: %4$s is location, %5$s is a description.
        $content = sprintf(_m('%1$s'),$description);

        // TRANS: Rendered event description. %1$s is a title, %2$s is start time, %3$s is start time,
	// TRANS: %4$s is end time, %5$s is end time, %6$s is location, %7$s is description.
	// TRANS: Class names should not be translated.
        $rendered = sprintf(_m('<span class="vevent">'.
                              '<span class="description">%1$s</span> '.
                              '</span>'),
                            htmlspecialchars($description));

        $options = array_merge(array('object_type' => Tips::OBJECT_TYPE),
                               $options);

        if (!array_key_exists('uri', $options)) {
            $options['uri'] = $ev->uri;
        }


        $saved = Notice::saveNew($profile->id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options);

        return $saved;
    }

  function stream($offset=0, $limit=NOTICES_PER_PAGE, $since_id=0, $max_id=0)
    {
        $stream = new TipsNoticeStream($this);
        return null;
    }

    function getNotice()
    {
        return Notice::staticGet('uri', $this->uri);
    }

    static function fromNotice($notice)
    {
        return Tips::staticGet('uri', $notice->uri);
    }

    function getSubcribes()
    {
        return Subscribe::forTip($this);
    }

    function getSubscribe($profile)
    {
        return Subscribe::pkeyGet(array('profile_id' => $profile->id,
                                   'tip_id' => $this->id));
    }
}
