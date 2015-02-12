<?php

/**
 * @author Samier Sompura <>
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Session\Container;

/**
 * View helper for Restaurant data
 */
class FlashMessager extends AbstractHelper
{

		protected static $_flashMessenger;

		/**
		 *	Get Flash Messager object in Layout.
		 *  @param $namespace.
		 *  @return \Zend\Mvc\Controller\Plugin\FlashMessenger Object.
		 */
    public function __invoke($namespace = 'default') 
		{
    	if (!self::$_flashMessenger) 
			{
      	self::$_flashMessenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger;
      }

      return self::$_flashMessenger->setNamespace($namespace);
    }
}