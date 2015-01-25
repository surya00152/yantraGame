<?php

Namespace User;

return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
        )
    ),
    'router' => array(
        'routes' => array(
            'User' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\User',
                        'action' => 'index'
                    ),
                ),
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'User\Model\User' => 'User\Model\User',
            'User\Model\DrowYantra' => 'User\Model\DrowYantra',
            'User\Model\TicketDate' => 'User\Model\TicketDate',
            'User\Model\Ticket' => 'User\Model\Ticket',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view'
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'UserPlugin' => 'User\Controller\Plugin\UserPlugin',
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
