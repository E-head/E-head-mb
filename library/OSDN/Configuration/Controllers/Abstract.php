<?
abstract class OSDN_Configuration_Controllers_Abstract extends OSDN_Controller_Action
{
    
    /**
     * @var OSDN_Configuration
     */
    protected $conf;
    
    public function init()
    {
        $this->conf = new OSDN_Configuration();
        parent::init();
    }
    
    public function loadSystemSettingsAction()
    {
        $response = $this->conf->getSystemSettings();
        if ($response->isError()) {
            $this->_collectErrors($response);
            return;
        }
        $this->view->data = array($response->data);
    }
    
    public function saveSystemSettingsAction()
    {
        $response = $this->conf->saveSystemSettings($this->_getAllParams());
        if ($response->isError()) {
            $this->_collectErrors($response);
            $this->view->success = false;
            return;
        }
        $this->view->success = true;
    }
    
    public function loadProfileSettingsAction()
    {
        $response = $this->conf->getProfileSettings();
        if ($response->isError()) {
            $this->_collectErrors($response);
            return;
        }
        $this->view->data = array($response->data);
    }
    
    public function saveProfileSettingsAction()
    {
        $response = $this->conf->saveProfileSettings($this->_getAllParams());
        if ($response->isError()) {
            $this->_collectErrors($response);
            $this->view->success = false;
            return;
        }
        $this->view->success = true;
    }
}