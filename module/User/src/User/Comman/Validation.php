<?php

namespace User\Comman;

use Zend\InputFilter\InputFilter;

/**
 * Comman Validation Class for User Module
 */
class Validation
{

    /**
     * Check Login Validation. 
     * @return Zend\InputFilter\InputFilter Object.
     */
    public static function loginCheckValidation()
    {
        $inputFilter = new InputFilter();

        //Token Validation
        $inputFilter->add(array(
            'name' => 'token',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'Token cannot be empty.'
                    )
                )
            )
        ));

        return $inputFilter;
    }
    
    /**
     * Login Validation. 
     * @return Zend\InputFilter\InputFilter Object.
     */
    public static function loginValidation()
    {
        $inputFilter = new InputFilter();

        //user-Name Validation
        $inputFilter->add(array(
            'name' => 'phoneNo',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'User-Name cannot be empty.'
                    )
                )
            )
        ));


        //password Validation
        $inputFilter->add(array(
            'name' => 'password',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'Password cannot be empty.'
                    )
                )
            )
        ));

        //token Validation
        $inputFilter->add(array(
            'name' => 'deviceId',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'Device not found.'
                    )
                )
            )
        ));

        return $inputFilter;
    }
    
    /**
     * Set Signup Validation. 
     * @return Zend\InputFilter\InputFilter Object.
     */
    public static function signupValidation()
    {
        $inputFilter = new InputFilter();

        //Name Validation
        $inputFilter->add(array(
            'name' => 'name',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'Name cannot be empty.'
                    )
                )
            )
        ));
        
        //user-Name Validation
        $inputFilter->add(array(
            'name' => 'phoneNo',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                    'options' => array(
                        'message' => 'Phone No cannot be empty.'
                    )
                )
            )
        ));

        return $inputFilter;
    }

}

?>