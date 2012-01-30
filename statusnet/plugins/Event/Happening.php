<?php
/**
 * Data class for happenings
 *
 * PHP version 5
 *
 * @category Data
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2011, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET')) {
    exit(1);
}

/**
 * Data class for happenings
 *
 * There's already an Event class in lib/event.php, so we couldn't
 * call this an Event without causing a hole in space-time.
 *
 * "Happening" seemed good enough.
 *
 * @category Event
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * @see      Managed_DataObject
 */
class Happening extends Managed_DataObject
{
    const OBJECT_TYPE = 'http://activitystrea.ms/schema/1.0/event';

    public $__table = 'stepcount'; // table name
    public $id;                    // varchar(36) UUID
    public $uri;                   // varchar(255)
    public $profile_id;            // int
    public $step_count;
    public $step_date;
    public $points_earned;
   //public $step_time;
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
        return Memcached_DataObject::staticGet('Happening', $k, $v);
    }

/** GP **/
 public static function pkeyGetStepCount($kv)
    {
        return Memcached_DataObject::pkeyGet('Happening', $kv);
    }

/** GP **/

    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
        return array(
            'description' => 'A real-world happening',
            'fields' => array(
                'id' => array('type' => 'char',
                              'length' => 36,
                              'not null' => true,
                              'description' => 'UUID'),
                'uri' => array('type' => 'varchar',
                               'length' => 255,
                               'not null' => true),
                'profile_id' => array('type' => 'int', 'not null' => true),
                'step_count' => array('type' => 'int', 'not null' => true),
                'points_earned' => array('type' => 'int', 'not null' => true),
                'step_date' => array('type' => 'datetime', 'not null' => true),
                //'step_time' => array('type' => 'datetime', 'not null' => true),
              
                'description' => array('type' => 'text'),
                'created' => array('type' => 'datetime',
                                   'not null' => true),
            ),
            'primary key' => array('id'),
            'unique keys' => array(
                'happening_uri_key' => array('uri'),
            ),
            'foreign keys' => array('happening_profile_id__key' => array('profile', array('profile_id' => 'id'))),
            'indexes' => array('happening_created_idx' => array('created'),
                               'happening_date_time_idx' => array('step_date')),
        );
    }

    static function saveNew($profile, $step_count, $step_date, $description, $options=array())
    {
        if (array_key_exists('uri', $options)) {
            $other = Happening::staticGet('uri', $options['uri']);
            if (!empty($other)) {
                // TRANS: Client exception thrown when trying to create an event that already exists.
                throw new ClientException(_m('Event already exists.'));
            }
        }

        $ev = new Happening();

        $ev->id          = UUID::gen();
        $ev->profile_id  = $profile->id;
        $ev->step_count  = $step_count;
        //Formula to calculate points earned
        $points_obj = UserPoints::getPoints($profile->id);
        if($points_obj != null)
        $points_index = pow(10,($points_obj->points_index - 1));
        else
        $points_index = 1;
        $points_earned = ($step_count / $points_index ) + ($step_count % $points_index);
        $ev->points_earned = $points_earned;
        $ev->step_date    = common_sql_date($step_date);

        //$ev->step_date = date('Y-m-d',strtotime(str_replace('/','-',$step_date)));
       // $ev->step_time    = common_sql_date($step_time);
        $ev->description = $description;


        if (array_key_exists('created', $options)) {
            $ev->created = $options['created'];
        } else {
            $ev->created = common_sql_now();
        }

        if (array_key_exists('uri', $options)) {
            $ev->uri = $options['uri'];
        } else {
            $ev->uri = common_local_url('showevent',
                                        array('id' => $ev->id));
        }

        $ev->insert();

        // XXX: does this get truncated?

        // TRANS: Event description. %1$s is a title, %2$s is start time, %3$s is end time,
	// TRANS: %4$s is location, %5$s is a description.
        $content = sprintf(_m('"%1$s" %2$s %3$s %4$s'),
                           $description,
                           common_exact_date($ev->step_date), $step_count,$points_earned
                           );

        // TRANS: Rendered event description. %1$s is a title, %2$s is start time, %3$s is start time,
	// TRANS: %4$s is end time, %5$s is end time, %6$s is location, %7$s is description.
	// TRANS: Class names should not be translated.
        $rendered = sprintf(_m('<span class="vevent">'),
                            htmlspecialchars($description)
                           );

        $options = array_merge(array('object_type' => Happening::OBJECT_TYPE),
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

    function getNotice()
    {
        return Notice::staticGet('uri', $this->uri);
    }

  function pkeyGet($kv)
    {
        return Memcached_DataObject::pkeyGet('RSVP', $kv);
    }

    static function fromNotice($notice)
    {
        return Happening::staticGet('uri', $notice->uri);
    }

    function getRSVPs()
    {
        return RSVP::forEvent($this);
    }

    function getRSVP($profile)
    {
        return RSVP::pkeyGet(array('profile_id' => $profile->id,
                                   'event_id' => $this->id));
    }
}
