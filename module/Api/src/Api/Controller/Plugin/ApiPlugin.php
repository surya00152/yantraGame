<?php

/**
 * @author Jack
 */

namespace Api\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 *  Api Plugin
 */
class ApiPlugin extends AbstractPlugin
{

    protected $unAuthorizeMethods = array(
        'login',
        'signup',
        'forgotPassword',
        'updateForgotPassword'
    );
    
    protected $authorizeMethods = array(
        'getUserDashboard',
        'createTicket',
        'getDrowStatus',
        'getPurchaseReport',
        'getDaywiseReport',
        'getShowwiseReport',
        'changePassword',
        'transferBalance',
        'sendTransferCode',
        'getNotification',
        'transactionReport'
    );

    /*
     * Check is valid method for access control
     */
    public function isValidMethod($post)
    {
        $response = array();
        if (in_array($post['method'], $this->unAuthorizeMethods)) {
            //if is unauthorize method
            $response['status'] = 'success';
        } else if (in_array($post['method'], $this->authorizeMethods)) {
             //if is authorize method then check isLogin
            return $this->getController()->userPlugin()->isLogin($post);
            
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid Method.';
        }
        return $response;
    }
    
    /*
     * Apply user action method
     */
    public function applyMethod($post)
    {
        $response = array();
        switch($post['method']) {
            case 'signup':
                $response = $this->getController()->userPlugin()->signup($post);
                break;
            case 'login':
                $response = $this->getController()->userPlugin()->login($post);
                break;
            case 'forgotPassword':
                $response = $this->getController()->userPlugin()->forgotPassword($post);
                break;
            case 'updateForgotPassword':
                $response = $this->getController()->userPlugin()->updateForgotPassword($post);
                break;
            case 'getUserDashboard':
                $response = $this->getController()->userPlugin()->getUserDashboard($post);
                break;
            case 'createTicket':
                $response = $this->getController()->userPlugin()->createTicket($post);
                break;
            case 'getDrowStatus':
                $response = $this->getController()->userPlugin()->getDrowStatus($post);
                break;
            case 'getPurchaseReport':
                $response = $this->getController()->userPlugin()->getPurchaseReport($post);
                break;
            case 'getDaywiseReport':
                $response = $this->getController()->userPlugin()->getDaywiseReport($post);
                break;
            case 'getShowwiseReport':
                $response = $this->getController()->userPlugin()->getShowwiseReport($post);
                break;
            case 'changePassword':
                $response = $this->getController()->userPlugin()->changePassword($post);
                break;
            case 'transferBalance':
                $response = $this->getController()->userPlugin()->transferBalance($post);
                break;
            case 'sendTransferCode':
                $response = $this->getController()->userPlugin()->sendTransferCode($post);
                break;
            case 'getNotification':
                $response = $this->getController()->userPlugin()->getNotification($post);
                break;
            case 'transactionReport':
                $response = $this->getController()->userPlugin()->transactionReport($post);
                break;
            default :
                $response['status'] = 'error';
                $response['message'] = 'Invalid Method.';
                break;
        }
        return $response;
    }
    
}