<?php
namespace Api;

class Module
{
	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
							),
					),		
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'apiHelper' => function($sm) {
					return new View\Helper\ApiHelper($sm); 
				}
			)
		); 
	}
}