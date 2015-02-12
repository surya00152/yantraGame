<?php
Namespace Api;

return array(
        'controllers' => array(
            'invokables' => array(
                'Api\Controller\ApiController'	=> 'Api\Controller\ApiController',
            )
        ),
		
	'router' => array(
		'routes' => array(
			'Api' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api',
                    'constraints' => array(
                    ),
                    'defaults' => array(
                        'controller' => 'Api\Controller\ApiController',
                        'action' => 'index'
                    ),
                ),
            ),
		)				
	),

	'service_manager' => array(
		'invokables' => array(
			//'Api\Model\Users' => 'Api\Model\Users',
		 )
	),
    
	'view_manager' => array(
		'template_path_stack' => array(
			'api' => __DIR__ . '/../view'
		)
	),

	'controller_plugins' => array(
		'invokables' => array(
			'apiPlugin' => 'Api\Controller\Plugin\ApiPlugin',
		)
	),
	
	'template_map' => array(
     	'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
	),

	// Doctrine config
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Model\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		)
	)		
);