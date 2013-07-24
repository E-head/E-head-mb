<?php

class OSDN_Configuration extends OSDN_Configuration_Abstract
{
    protected $_allowedParams = array(
        'scanner_imageType'         => false,
    	'scanner_pixelType'         => false,
    	'scanner_Resolution'        => false,
        'scanner_ScanSource'        => false,
        'scanner_IfFeederEnabled'   => false,
        'scanner_IfDuplexEnabled'   => false,
        'scanner_ifShowUI'          => false,
    
    
    	'comments_notificator_template' => true,
    	'student_email_invitation_template' => true,
    	'student_email_invitation_template_title' => true,
    	'manager_email_invitation_template' => true,
    	'manager_email_invitation_template_title' => true,
    	'manager_pdf_email_invitation_template' => true,
   		'manager_pdf_email_invitation_template_title' => true,
    
        'last_datetime_of_catalogue_synchronization' => false,
        'reupdate_datetime_of_catalogue_synchronization' => false,
        'last_datetime_of_translation_synchronization' => false,
        'reupdate_datetime_of_translation_synchronization' => false,
    	'last_datetime_remind_hourregistration_input' => false,
        'last_datetime_send_remind_hourregistration_input' => false,
        'reminder_hr_email_template' => true,
        'reminder_hr_email_template_title' => true,
        'reminder_hr_grid_template' => true,
        'reminder_stop_study_email_template' => true,
        'reminder_stop_study_email_template_title' => true,
        'reminder_stop_study_grid_template' => true
    );
}
