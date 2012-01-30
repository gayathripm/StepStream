<?php
/**
 * @category  Tips
 * @package   StatusNet
 * @author    Gayathri Premachandran 
 */

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

/**
 * Cancel subscription for an tip
 */
class CancelsubscribeAction extends Action
{
    protected $user  = null;
    protected $subscribe  = null;
    protected $tip = null;

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {

        return _m('TITLE','Cancel Subscribe');
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
        if ($this->boolean('ajax')) {
            StatusNet::setApi(true); // short error results!
        }

        $subId = $this->trimmed('subscribe');

        if (empty($subId)) {
            // TRANS: Client exception thrown when referring to a non-existing Subscription
            throw new ClientException(_m('No such Subscription.'));
        }

        $this->subscribe = Subscribe::staticGet('id', $subId);

        if (empty($this->subscribe)) {
            // TRANS: Client exception thrown when referring to a non-existing Subscription
            throw new ClientException(_m('No such Subscription.'));
        }

        $this->tip = Tips::staticGet('id', $this->subscribe->tip_id);

        if (empty($this->tip)) {
            // TRANS: Client exception thrown when referring to a non-existing tip.
            throw new ClientException(_m('No such tip.' ));
        }

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Client exception thrown when trying to subscribe while not logged in.
            throw new ClientException(_m('You must be logged in to subscribe to a tip.'));
        }

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

        if ($this->isPost()) {
            $this->cancelSubcribe();
        } else {
            $this->showPage();
        }

        return;
    }

    /**
     * 
     *
     * @return void
     */
    function cancelSubcribe()
    {
        try {
            $notice = $this->subscribe->getNotice();
            // NB: this will delete the subscribe, too
            if (!empty($notice)) {
                common_log(LOG_DEBUG, "Deleting notice...");
                $notice->delete();
            } else {
                common_log(LOG_DEBUG, "Deleting subscribe alone...");
                $this->subscribe->delete();
            }
        } catch (ClientException $ce) {
            $this->error = $ce->getMessage();
            $this->showPage();
            return;
        }

        if ($this->boolean('ajax')) {
            header('Content-Type: text/xml;charset=utf-8');
            $this->xw->startDocument('1.0', 'UTF-8');
            $this->elementStart('html');
            $this->elementStart('head');
            // TRANS: Page title after sending a notice.
            $this->element('title', null, _m('Tip saved'));
            $this->elementEnd('head');
            $this->elementStart('body');
            $this->elementStart('body');
            $form = new SubscribeTipForm($this->tip, $this);
            $form->show();
            $this->elementEnd('body');
            $this->elementEnd('body');
            $this->elementEnd('html');
        }
    }

    /**
     * Show the tip form
     *
     * @return void
     */
    function showContent()
    {
        if (!empty($this->error)) {
            $this->element('p', 'error', $this->error);
        }

        $form = new CancelSubscribeForm($this->subscribe, $this);

        $form->show();

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
    function isReadOnly($args)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET' ||
            $_SERVER['REQUEST_METHOD'] == 'HEAD') {
            return true;
        } else {
            return false;
        }
    }
}
