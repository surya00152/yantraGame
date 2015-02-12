<?php

namespace Addressbook\Comman;

use Zend\InputFilter\InputFilter;

/**
 * Comman Validation Class for AddressBook Module
 */

class Validation
{
    /**
    * Set Add Contact Validation. 
    * @return Zend\InputFilter\InputFilter Object.
    */		 
	public static function addContactsValidation ()
	{
		$inputFilter = new InputFilter();
		
		// Adding First Name Validation
		$inputFilter->add(array(
			'name' => 'firstName',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'First Name cannot be empty.'
					)								
				),*/
				array(
				'name' => 'regex', 
				'options'=>array(
				'pattern' => '/^[a-zA-Z ]+$/',
					'message' => 'Only letters allow in First Name.'
				)
			)
		)
	));	
		
		
		// Add Last Name Validation
		$inputFilter->add(array(
			'name' => 'lastName',
			//'required' => true,
			'validators' => array(
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^[a-zA-Z ]+$/',
						'message' => 'Only letters allow in Last Name.'
					 )
				),
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Last Name cannot be empty.'
					)			
				)*/
			)
		));
		
		// Adding Email Validation
		$inputFilter->add(array(
			'name' => 'email',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Email Address cannot be empty.'
					)								
				),*/
				array(
					'name' => 'email_address',
					'options' => array(
						'message' => 'Please enter valid email address.'
					)
				),
			)
		));	
		
		
		// Adding Phone No. Validation
		$inputFilter->add(array(
			'name' => 'phoneSMS',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Phone No cannot be empty.'
					)								
				),*/
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^([0-9\-])+$/',
						'message' => 'Please enter valid Phone no.'
					)
				)
			)
		));
		
		// Adding City Validation
		$inputFilter->add(array(
			'name' => 'city',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
					'message' => 'City name cannot be empty.'
					)								
				),*/
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^[a-zA-Z ]+$/',
						'message' => 'Only letters allow in City.'
					 )
				)
			)
		));	

		// Adding State Validation
		$inputFilter->add(array(
			'name' => 'state',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'State Name cannot be empty.'
					)								
				),*/
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^[a-zA-Z ]+$/',
						'message' => 'Only letters allow in State.'
					 )
				)
			)
		));	

		// Adding Country Validation
		$inputFilter->add(array(
			'name' => 'Country',
			//'required' => true,
			'validators' => array(
				/*array(
					'name' => 'not_empty',
					'options' => array(
						'message' => 'Country Name cannot be empty.'
					)								
				),*/
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^[a-zA-Z ]+$/',
						'message' => 'Only letters allow in Country.'
					 )
				)
			)
		));	

		// Adding Company Validation
		$inputFilter->add(array(
			'name' => 'company',
			//'required' => true,
			'validators' => array(
				/*array(
						'name' => 'not_empty',
						'options' => array(
						'message' => 'Company Name cannot be empty.'
					)								
				),*/
				array(
					'name' => 'regex', 
					'options'=>array(
						'pattern' => '/^[a-zA-Z ]+$/',
						'message' => 'Only letters allow in Company Name.'
					 )
				)
			)
		));	
				
		return $inputFilter;	
	}
}
?>