<?php
/*
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2008, 2009, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('STATUSNET') && !defined('LACONICA')) { exit(1); }

// You have 24 hours to claim your password

//define('MAX_RECOVERY_TIME', 24 * 60 * 60);

class RecoverpasswordAction extends Action
{
    protected $user        = null;
    protected $error       = null;
    protected $complete    = null;
    protected $step_count  = null;
    protected $step_date    = null;
    //protected $step_time    = null;
    protected $description = null;
  

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {
        // TRANS: Title for new event form.
        return _m('TITLE','New event');
    }

    /**
     * For initializing members of the class.
     *
     * @param array $argarray misc. arguments
     *
     * @return boolean true
     */
    function prepare($argarray)
    {
        parent::prepare($argarray);

     
        return true;
    }

    /**
     * Handler method
     *
     * @param array $argarray is ignored since it's now passed in in prepare()
     *
     * @return void
     */
    function handle($argarray=null)
    {
        parent::handle($argarray);

       $ev = new Happening();
$description = "Sent from twilio";
        $ev->id          = UUID::gen();

$input_message = $_REQUEST['Body'];
	$input_arr = explode(' ' , $input_message);
	$ev->profile_id = $input_arr[0];
	$ev->step_count = $input_arr[1];


  //      $ev->profile_id  = $_REQUEST['user_id'];
//        $ev->step_count  = $_REQUEST['step_count'];
        $ev->description = $description;
$points_obj = UserPoints::getPoints($this->user_id);
        if($points_obj != null)
        $points_index = pow(10,($points_obj->points_index - 1));
        else
        $points_index = 1;
        $points_earned = ($step_count / $points_index ) + ($step_count % $points_index);
        $ev->points_earned = $points_earned; 


                              $ev->step_date    = "02/07/2012";
            $ev->created = common_sql_now();
        $ev->uri = common_local_url('showevent',
                                        array('id' => $ev->id));

       

        $ev->insert();

        // XXX: does this get truncated?

        // TRANS: Event description. %1$s is a title, %2$s is start time, %3$s is end time,
	// TRANS: %4$s is location, %5$s is a description.
        $content = sprintf(_m('"%1$s" %2$s %3$s'),
                           $description,
                           common_exact_date($ev->step_date),$ev->step_count
                           );

        // TRANS: Rendered event description. %1$s is a title, %2$s is start time, %3$s is start time,
	// TRANS: %4$s is end time, %5$s is end time, %6$s is location, %7$s is description.
	// TRANS: Class names should not be translated.
        $rendered = sprintf(_m('<span class="vevent">'),
                            htmlspecialchars($description)
                           );

        $options = array_merge(array('object_type' => Happening::OBJECT_TYPE),
                               $options);

        

       

        $saved = Notice::saveNew($ev->profile_id,
                                 $content,
                                 array_key_exists('source', $options) ?
                                 $options['source'] : 'web',
                                 $options); 


        return;
    }

   
    function showContent()
    {
    

        return;
    }

    /**
     * Return true if read only.
     *
     * MAY override
     *
     * @param array $args other arguments
     *
     * @return boolean is read only action?
     */
     function showNotice($notice)
    {
        $nli = new NoticeListItem($notice, $this);
        $nli->show();
    }

}
