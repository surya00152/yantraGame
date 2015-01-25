<?php

/**
 * @author Jack
 */

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ViewModel\JsonModel;


/**
 * Api Controller
 */
class ApiController extends AbstractActionController
{
    /*
     * Default index action call
     */

    public function indexAction()
    {
        $data = array();
        /*$data = array(
            'method' => 'getDrowStatus',
            'token' => 'ac17ca8e532446abb4f5b0a867d635147ea6c70f'
        );*/
  
        //Check post method.
        if ($this->getRequest()->isPost() || count($data) > 0) {
            $response = array();
            //Get Post Value
            $post = (count($data) > 0) ? $data : $this->getRequest()->getPost();
            
            //Check Method is empty or not
            if (!empty($post['method'])) {
                
                $isValid = $this->apiPlugin()->isValidMethod((array) $post);
                if ($isValid['status'] == 'success') {
                    
                    $response = $this->apiPlugin()->applyMethod((array) $post);
                } else {
                    $response = $isValid;
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = '(method) param required.';
            }
        } else {
            //Set Error code for redirect to 404 Page.
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        //echo '<pre>';print_r($response);exit;
        exit(json_encode($response));
    }

}