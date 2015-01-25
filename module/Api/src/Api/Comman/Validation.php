<?php
namespace API\Comman;

use Zend\InputFilter\InputFilter;

/**
 * Comman Validation Class for Menu Module
 */

class Validation
{
   /**
    * Set Validation for Message. 
    * @return Zend\InputFilter\InputFilter Object.
    */	
   public static function MessageValidation ()
	{
		$inputFilter = new InputFilter();
		
		$inputFilter->add(array(
			'name' => 'receiverId',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'You must input atleast one Recipient.'
					)								
				),
				array(
				'name' => 'regex', 
				'options'=>array(
					'pattern' => '/^[0-9 ,]+$/',
					'message' => 'Receiver Id is not in the correct format, it should just be a Number.'
					),
				)
			)
		));		

		$inputFilter->add(array(
			'name' => 'subject',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Subject Can Not Be Blank.'
					)								
				),
			)
		));		

		$inputFilter->add(array(
			'name' => 'messageContent',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Message Content Can Not Be Empty.'
					)								
				),
			)
		));		

		$inputFilter->add(array(
			'name' => 'senderId',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Sender Can Not Be Empty.'
					)								
				),
				array(
				'name' => 'regex', 
				'options'=>array(
					'pattern' => '/^[0-9]+$/',
					'message' => 'Sender Id is not in the correct format, it should just be a Number.'
					)
				),
			)
		));		
			
		return $inputFilter;
	}

   /**
    * Set Validation for Comments. 
    * @return Zend\InputFilter\InputFilter Object.
    */	
	public static function CommentsValidation ()
	{
		$inputFilter = new InputFilter();
	
		$inputFilter->add(array(
			'name' => 'messageId',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Message Id Can Not Be Empty.'
					)								
				),
				array(
				'name' => 'regex', 
				'options'=>array(
					'pattern' => '/^[0-9]+$/',
					'message' => 'Message Id is not in the correct format, it should just be a Number.'
					)
				),
			)
		));		

		$inputFilter->add(array(
			'name' => 'subject',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Subject Can Not Be Blank.'
					)								
				),
			)
		));	

		$inputFilter->add(array(
			'name' => 'messageContent',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Message Content Can Not Be Empty.'
					)								
				),
			)
		));	

		$inputFilter->add(array(
			'name' => 'senderId',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Sender Can Not Be Empty.'
					)								
				),
				array(
				'name' => 'regex', 
				'options'=>array(
					'pattern' => '/^[0-9]+$/',
					'message' => 'Sender Id is not in the correct format, it should just be a Number.'
					)
				),
			)
		));	
			
		return $inputFilter;
	}
	
   /**
    * Set Validation for Registration.
    * @return Zend\InputFilter\InputFilter Object.
    */	
	public static function registrationValidation ()
	{
		$inputFilter = new InputFilter();
		
		// Adding User/Email Address Validation
		$inputFilter->add(array(
			'name' => 'email',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Email address should not be blank.'
						)								
				  ),
				array(
					'name' => 'email_address',
					'options' => array(
						'message' => 'Please enter valid email address.'
						)
					 )
			 )
		));

		// Adding Password Validation
		$inputFilter->add(array(
			'name' => 'password',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Password should not be blank.'
					)								
				),
				array(
					'name' => 'string_length',
					'options' => array(
						'min' => 6,
						'message' => 'Password must be six characters long.'
					)
				)
			)
		));
		
		// Adding FirstName Validation
		$inputFilter->add(array(
			'name' => 'firstName',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Name should not be blank.'
					)								
				)
			),
		));

		return $inputFilter;
	}
}
?>