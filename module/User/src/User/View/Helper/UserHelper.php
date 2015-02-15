<?php

/**
 * @author Jack 
 */

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
/**
 * View helper for Store.
 */
class UserHelper extends AbstractHelper
{
	/**             
	 * @var Service Manager Object
	 */                
	protected $serviceManager;
	
	/**
	 *  Call Constructor To Get Service Manager Object   
	 */
	public function __construct($serviceManager) 
	{
		// Set Service Manaer Object
		$this->serviceManager = $serviceManager;
	}
}