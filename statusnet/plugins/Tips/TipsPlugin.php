<?php


if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Tips plugin
 *
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 */
class TipsPlugin extends MicroappPlugin
{

    function onCheckSchema()
    {
        $schema = Schema::get();

        $schema->ensureTable('tips', Tips::schemaDef());
        $schema->ensureTable('tips_subscribe', Subscribe::schemaDef());

        return true;
    }

    /**
     * Load related modules when needed
     *
     * @param string $cls Name of the class to be loaded
     *
     * @return boolean hook value; true means continue processing, false means stop.
     */
    function onAutoload($cls)
    {
        $dir = dirname(__FILE__);

        switch ($cls)
        {
        case 'NewtipsAction':
        case 'NewsubscribeAction':
        case 'CancelsubscribeAction':
        case 'ShowtipsAction':
        case 'ShowsubscribeAction':
            include_once $dir . '/' . strtolower(mb_substr($cls, 0, -6)) . '.php';
            return false;
        case 'TipsListItem':
        case 'SubscribeListItem':
        case 'TipsForm':
        case 'SubscribeTipForm':
        case 'CancelSubscribeForm':
            include_once $dir . '/'.strtolower($cls).'.php';
            break;
        case 'Tips':
        case 'Subscribe':
            include_once $dir . '/'.$cls.'.php';
            return false;
        default:
            return true;
        }
    }

    /**
     * Map URLs to actions
     *
     * @param Net_URL_Mapper $m path-to-action mapper
     *
     * @return boolean hook value; true means continue processing, false means stop.
     */
    function onRouterInitialized($m)
    {
        $m->connect('main/tips/new',
                    array('action' => 'newtips'));
        $m->connect('main/tips/subscribe',
                    array('action' => 'newsubscribe'));
        $m->connect('main/tips/subscribe/cancel',
                    array('action' => 'cancelsubscribe'));
        $m->connect('tips/:id',
                    array('action' => 'showtips'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));
        $m->connect('subscribe/:id',
                    array('action' => 'showsubscribe'),
                    array('id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'));
        return true;
    }

    function onPluginVersion(&$versions)
    {
        $versions[] = array('name' => 'Tips',
                            'version' => STATUSNET_VERSION,
                            'author' => 'Gayathri Premachandran',
                            'description' =>
                            // TRANS: Plugin description.
                            _m('Tips and Subscriptions'));
        return true;
    }

    function appTitle() {
        // TRANS: Title for tip application.
        return _m('TITLE','Tips');
    }

    function tag() {
        return 'tips';
    }

    function types() {
        return array(Tips::OBJECT_TYPE,
                     Subscribe::POSITIVE,
                     Subscribe::NEGATIVE,
                     Subscribe::POSSIBLE);
    }

    /**
     * Given a parsed ActivityStreams activity, save it into a notice
     * and other data structures.
     *
     * @param Activity $activity
     * @param Profile $actor
     * @param array $options=array()
     *
     * @return Notice the resulting notice
     */
    function saveNoticeFromActivity($activity, $actor, $options=array())
    {
        if (count($activity->objects) != 1) {
            // TRANS: Exception thrown when there are too many activity objects.
            throw new Exception(_m('Too many activity objects.'));
        }

        $tipObj = $activity->objects[0];

        if ($tipObj->type != Tips::OBJECT_TYPE) {
            // TRANS: Exception thrown when event plugin comes across a non-tip type object.
            throw new Exception(_m('Wrong type for object.'));
        }

        $notice = null;

        switch ($activity->verb) {
        case ActivityVerb::POST:
        	// FIXME: get startTime, endTime, location and URL
            $notice = Tips::saveNew($actor,$tipObj->summary,$options);
            break;
        case Subscribe::POSITIVE:
        case Subscribe::NEGATIVE:
        case Subscribe::POSSIBLE:
            $tip = Tips::staticGet('uri',$tipObj->id);
            if (empty($tip)) {
                // FIXME: save the tip
                // TRANS: Exception thrown when trying to Subscribe for an unknown tip.
                throw new Exception(_m('Subscribe for unknown tip.'));
            }
            $notice = Subscribe::saveNew($actor, $tip, $activity->verb, $options);
            break;
        default:
            // TRANS: Exception thrown when tip plugin comes across a undefined verb.
            throw new Exception(_m('Unknown verb for tips.'));
        }

        return $notice;
    }

    /**
     * Turn a Notice into an activity object
     *
     * @param Notice $notice
     *
     * @return ActivityObject
     */
   function activityObjectFromNotice($notice)
    {
        $tip = null;

        switch ($notice->object_type) {
        case Tips::OBJECT_TYPE:
            $tip = Tips::fromNotice($notice);
            break;
        case Subscribe::POSITIVE:
        case Subscribe::NEGATIVE:
        case Subscribe::POSSIBLE:
            $subscribe  = Subscribe::fromNotice($notice);
            $tip = $subscribe->getTip();
            break;
        }

        if (empty($tip)) {
            // TRANS: Exception thrown when tip plugin comes across a unknown object type.
            throw new Exception(_m('Unknown object type.'));
        }

        $notice = $tip->getNotice();

        if (empty($notice)) {
            // TRANS: Exception thrown when referring to a notice that is not an tip an in tip context.
            throw new Exception(_m('Unknown tip notice.'));
        }

        $obj = new ActivityObject();

        $obj->id      = $tip->uri;
        $obj->type    = Tips::OBJECT_TYPE;
        
        $obj->summary = $tip->description;
        $obj->link    = $notice->bestUrl();


        return $obj;
    }


    /**
     * Change the verb on Subscribe notices
     *
     * @param Notice $notice
     *
     * @return ActivityObject
     */
    function onEndNoticeAsActivity($notice, &$act) {
        switch ($notice->object_type) {
        case Susbcribe::POSITIVE:
        case Subscribe::NEGATIVE:
        case Subscribe::POSSIBLE:
            $act->verb = $notice->object_type;
            break;
        }
        return true;
    }

    function adaptNoticeListItem($nli)
    {
        $notice = $nli->notice;

        switch ($notice->object_type) {
        case Tips::OBJECT_TYPE:
            return new TipsListItem($nli);
            break;
        case Susbcribe::POSITIVE:
        case Susbcribe::NEGATIVE:
        case Susbcribe::POSSIBLE:
            return new SusbcribeListItem($nli);
            break;
        }
        return null;
    }


    /**
     * Form for our app
     *
     * @param HTMLOutputter $out
     * @return Widget
     */
    function entryForm($out)
    {
        return new TipsForm($out);
    }

    /**
     * When a notice is deleted, clean up related tables.
     *
     * @param Notice $notice
     */
    function deleteRelated($notice)
    {
        switch ($notice->object_type) {
        case Tips::OBJECT_TYPE:
            common_log(LOG_DEBUG, "Deleting tip from notice...");
            $tip = Tips::fromNotice($notice);
            $tip->delete();
            break;
        case Subscribe::POSITIVE:
        case Subscribe::NEGATIVE:
        case Subscribe::POSSIBLE:
            common_log(LOG_DEBUG, "Deleting tip from notice...");
            $subscribe = Subscribe::fromNotice($notice);
            common_log(LOG_DEBUG, "to delete: $subscribe->id");
            $subscribe->delete();
            break;
        default:
            common_log(LOG_DEBUG, "Not deleting related, wtf...");
        }
    }

    function onEndShowScripts($action)
    {
        $action->script($this->path('tips.js'));
    }

    function onEndShowStyles($action)
    {
        $action->cssLink($this->path('tips.css'));
        return true;
    }

    function onStartAddNoticeReply($nli, $parent, $child)
    {
        // Filter out any poll responses
        if (($parent->object_type == Tips::OBJECT_TYPE) &&
            in_array($child->object_type, array(Subscribe::POSITIVE, Subscribe::NEGATIVE, Subscribe::POSSIBLE))) {
            return false;
        }
        return true;
    }
}
