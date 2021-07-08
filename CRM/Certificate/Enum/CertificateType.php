<?php
use CRM_Certificate_ExtensionUtil as E;

/**
 * Declares the constants for entities supported
 * by the certificate extension
 * 
 */
class CRM_Certificate_Enum_CertificateType
{

    const CASES = 1;

    /**
     * Returns the options to populate Entity select box
     * in the certificate configure form
     * 
     * @return array
     */
    public static function getOptions() {
        return array(
            ''  => E::ts('- select -'),
            self::CASES   => E::ts('Cases')
        );
    }

    /**
     * Return the fileds to populate the entity type reference field
     * for each supported entities in the certificate configure form
     * 
     * @return string - json encoded array of the entity type field options
     */
    public static function getEnityRefs() {
        return json_encode(array(
            self::CASES => [
                'entity' => 'caseType',
                'placeholder' => ts('- Select Case Type -'),
                'select'=> [
                    'minimumInputLength' => 0,
                    'multiple'=> true
                ],
            ]
        ));
    }

    /**
     * Return the fileds to populate the entity status reference field
     * for each supported entities in the certificate configure form
     * 
     * @return string - json encoded array of the entity status field options
     */
    public static function getEntityStatusRefs() {
        return json_encode(array(
            self::CASES => [
                'placeholder' => ts('- Select Case Status  -'),
                'entity' => 'OptionValue',
                'api' => [
                    'params' => [
                        'option_group_id' => "case_status",
                    ]
                ],
                'select'=> [
                    'minimumInputLength' => 0,
                    'multiple'=> true
                ]
            ]
        ));
    }
}