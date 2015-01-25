<?php

Namespace Cronejob;

return array(
    'controllers' => array(
        'invokables' => array(
            'Cronejob\Controller\Cronejob' => 'Cronejob\Controller\CronejobController',
        )
    ),
    'router' => array(
        'routes' => array(
            'cronejob' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/crone-job[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Cronejob\Controller\Cronejob',
                        'action' => 'index'
                    ),
                ),
            )
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'CronejobPlugin' => 'Cronejob\Controller\Plugin\CronejobPlugin',
        )
    )
);
