<?php

class ErrorController extends OSDN_Controller_Action
{

    const PERMISSION_CLASS = 'Zend_Acl_Exception';
    
    public function deniedAction()
    {
        $trace = $this->_getParam('trace');
        if (is_array($trace)) {
            $traceCollection = array();
            
            $resourceModel = new OSDN_Acl_Resource();

            foreach($trace as $log) {
                if (2 != count($log)) {
                    continue;
                }
                
                list($resource, $privilege) = $log;
                $response = $resourceModel->fetchResourceNamesRecursive($resource);
                if ($response->isSuccess()) {
                    
                    $privilegeName = OSDN_Acl_Privilege::id2name($privilege);
                    if (!empty($privilegeName)) {
                        $privilegeName = lang(ucfirst($privilegeName));
                    }
                    
                    $traceCollection[] = array(
                        'resource'  => $response->rowset,
                        'privilege' => $privilegeName
                    );
                }
            }

            $this->view->trace = $traceCollection;
        }
        
        header($_SERVER['SERVER_PROTOCOL'] . ' 405 Permission denied', false, 405);
        $this->disableLayout(true);
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($this->view->getEngine()) {
            case 'json':
            case 'xml':
                
                switch ($errors->type) {
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                        header('Not found.', false, 404);
                        break;
                        
                    default:
                        header('Internal server error', false, 500);
                        break;
                }
                break;
                
            default:
                
                
                if (self::PERMISSION_CLASS === get_class($errors->exception)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', null, 403);
                    break;
                }
                
                if (OSDN_DEBUG) {
                    $this->view->message = $errors->exception->getMessage();
                    $this->view->details = Zend_Debug::dump($errors, null, false);
                }
                
                switch ($errors->type) {
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                    case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                        $this->_forward('index');
                        break;
                    default:
                        $this->_forward('exception');
                        break;
                }
                break;
        }
    }
}
