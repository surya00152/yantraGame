<?php

Namespace Admin;

return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Admin' => 'Admin\Controller\AdminController',
        )
    ),
    'router' => array(
        'routes' => array(
            'Admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin[/:action][/:id][/:status]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'status' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Admin',
                        'action' => 'login'
                    ),
                ),
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Admin\Model\Yantra' => 'Admin\Model\Yantra',
            'Admin\Model\Admin' => 'Admin\Model\Admin',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view'
        )
    ),
    'module_layouts' => array(
        'Admin' => 'layout/layout',
    ),
    'template_map' => array(
        'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'AdminPlugin' => 'Admin\Controller\Plugin\AdminPlugin',
        )
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
