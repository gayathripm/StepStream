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
 * Add a new t‬ip
 */
class NewtipsAction extends Action
{
    protected $user        = null;
    protected $error       = null;
    protected $complete    = null;
    protected $description = null;

    /**
     * Returns the title of the action
     *
     * @return string Action title
     */
    function title()
    {
        // TRANS: Title for new tip form.
        return _m('TITLE','New tip');
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

        $this->user = common_current_user();

        if (empty($this->user)) {
            // TRANS: Client exception thrown when trying to post an tip while not logged in.
            throw new ClientException(_m('Must be logged in to post a tip.'),
                                      403);
        }

        if ($this->isPost()) {
            $this->checkSessionToken();
        }

        try {

            $this->description = $this->trimmed('description');

            
        } catch (ClientException $ce) {
            if ($this->boolean('ajax')) {
                $this->outputAjaxError($ce->getMessage());
                return false;
            } else {
                $this->error = $ce->getMessage();
                $this->showPage();
                return false;
            }
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
            $this->newTip();
        } else {
            $this->showPage();
        }

        return;
    }

    /**
     * Add a new tip
     *
     * @return void
     */
    function newTip()
    {
        try {
            if (empty($this->description)) {
                // TRANS: Client exception thrown when trying to post an tip without providing a description.
                throw new ClientException(_m('Tip must have a description.'));
            }


            $options = array();

            // Does the heavy-lifting for getting "To:" information

            ToSelector::fillOptions($this, $options);

            $profile = $this->user->getProfile();

            $saved = Tips::saveNew($profile,
                                        $this->description,
                                        $options);

            $tip = Tips::fromNotice($saved);

            Subscribe::saveNew($profile, $tip, Subscribe::POSITIVE);

        } catch (ClientException $ce) {
            if ($this->boolean('ajax')) {
                $this->outputAjaxError($ce->getMessage());
            } else {
                $this->error = $ce->getMessage();
                $this->showPage();
                return;
            }
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
            $this->showNotice($saved);
            $this->elementEnd('body');
            $this->elementEnd('html');
        } else {
            common_redirect($saved->bestUrl(), 303);
        }
    }

    // @todo factor this out into a base class
    function outputAjaxError($msg)
    {
        header('Content-Type: text/xml;charset=utf-8');
        $this->xw->startDocument('1.0', 'UTF-8');
        $this->elementStart('html');
        $this->elementStart('head');
        // TRANS: Page title after an AJAX error occurs
        $this->element('title', null, _('Ajax Error'));
        $this->elementEnd('head');
        $this->elementStart('body');
        $this->element('p', array('id' => 'error'), $msg);
        $this->elementEnd('body');
        $this->elementEnd('html');
        return;
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

        $form = new TipsForm($this);

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


    /**
     * Output a notice
     *
     * Used to generate the notice code for Ajax results.
     *
     * @param Notice $notice Notice that was saved
     *
     * @return void
     */
    function showNotice($notice)
    {
        $nli = new NoticeListItem($notice, $this);
        $nli->show();
    }
}
