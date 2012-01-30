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
 * Data class for userpoints
 *
 * There's already an Event class in lib/event.php, so we couldn't
 * call this an Event without causing a hole in space-time.
 *
 * "UserPoints" seemed good enough.
 *
 * @category Event
 * @package  StatusNet
 * @author   Evan Prodromou <evan@status.net>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://status.net/
 *
 * @see      Managed_DataObject
 */
class UserPoints extends Managed_DataObject
{
    const OBJECT_TYPE = 'http://activitystrea.ms/schema/1.0/event';

    public $__table = 'user_points'; // table name
  
    public $profile_id;            // int
    public $cumulative_points;
    public $available_points;
    public $points_index;
    public $nickname;
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
        return Memcached_DataObject::staticGet('UserPoints', $k, $v);
    }



    /**
     * The One True Thingy that must be defined and declared.
     */
    public static function schemaDef()
    {
         return array(
            'description' => 'A real-world happening',
            'fields' => array(
                'profile_id' => array('type' => 'int', 'not null' => true),
'nickname' => array('type' => 'varchar', 'length' => 64),
                'cumulative_points' => array('type' => 'int', 'not null' => true),
                'available_points' => array('type' => 'int', 'not null' => true),
                'points_index' => array('type' => 'int', 'not null' => true),
            ),
            'primary key' => array('profile_id'),
          
           
        );
       
    }

  static function getPoints($profile_id)
    {
        return UserPoints::staticGet('profile_id', $profile_id);
    }

  
}
