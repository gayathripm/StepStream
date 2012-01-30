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
 * Subscribe to a tips
 */
class NewsubscribeAction extends Action
{
    protected $user  = null;
    protected $tips = null;
    protected $verb  = null;

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {
        // TRANS: Title for Subscribe Action
        return _m('TITLE','New Subscribe');
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

        $tipId = $this->trimmed('tips');

        if (empty($tipId)) {
            // TRANS: Client exception thrown when referring to a non-existing tip.
            throw new ClientException(_m('No such tip.'));
        }

        $this->tips = Tips::staticGet('id', $tipId);

        if (empty($this->tips)) {
            // TRANS: Client exception thrown when referring to a non-existing tip.
            throw new ClientException(_m('No such tip.'));
        }

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Client exception thrown when trying to Subscribe while not logged in.
            throw new ClientException(_m('You must be logged in to Subscribe for a tip.'));
        }

        common_debug(print_r($this->args, true));

        switch (strtolower($this->trimmed('submitvalue'))) {
        case 'yes':
            $this->verb = Subscribe::POSITIVE;
            break;
        case 'no':
            $this->verb = Subscribe::NEGATIVE;
            break;
        case 'maybe':
            $this->verb = Subscribe::POSSIBLE;
            break;
        default:
            // TRANS: Client exception thrown when using an invalid value for Subscribe
            throw new ClientException(_m('Unknown submit value.'));
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
            $this->newSubscribe();
        } else {
            $this->showPage();
        }

        return;
    }

    /**
     * Add a new suscribe
     *
     * @return void
     */
    function newSubscribe()
    {
        try {
            $saved = Subscribe::saveNew($this->user->getProfile(),
                                   $this->tips,
                                   $this->verb);
        } catch (ClientException $ce) {
            $this->error = $ce->getMessage();
            $this->showPage();
            return;
        }

        if ($this->boolean('ajax')) {
            $subscribe = Subscribe::fromNotice($saved);
            header('Content-Type: text/xml;charset=utf-8');
            $this->xw->startDocument('1.0', 'UTF-8');
            $this->elementStart('html');
            $this->elementStart('head');
            // TRANS: Page title after creating an tip.
            $this->element('title', null, _m('Tip saved'));
            $this->elementEnd('head');
            $this->elementStart('body');
            $this->elementStart('body');
            $cancel = new CancelSubscribeForm($subscribe, $this);
            $cancel->show();
            $this->elementEnd('body');
            $this->elementEnd('body');
            $this->elementEnd('html');
        } else {
            common_redirect($saved->bestUrl(), 303);
        }
    }

    /**
     * Show the subscribe form
     *
     * @return void
     */


    function showContent()
    {
        if (!empty($this->error)) {
            $this->element('p', 'error', $this->error);
        }

        $form = new SubscribeTipForm($this->tips, $this);

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
