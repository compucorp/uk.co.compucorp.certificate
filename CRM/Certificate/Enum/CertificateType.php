<?php

use CRM_Certificate_ExtensionUtil as E;

/**
 * Declares the constants for entities supported
 * by the certificate extension
 *
 */
class CRM_Certificate_Enum_CertificateType {

  const CASES = 1;
  const EVENTS = 2;
  const MEMBERSHIPS = 3;

  /**
   * Returns the options to populate Entity select box
   * in the certificate configure form
   *
   * @return array
   */
  public static function getOptions() {
    return [
      ''  => E::ts('- Select -'),
      self::CASES   => E::ts('Cases'),
      self::EVENTS  => E::ts('Events'),
      self::MEMBERSHIPS => E::ts('Memberships'),
    ];
  }

  /**
   * Return the fileds to populate the entity type reference field
   * for each supported entities in the certificate configure form
   *
   * @return string - json encoded array of the entity type field options
   */
  public static function getEnityRefs() {
    return json_encode([
      self::CASES => [
        'entity' => 'caseType',
        'placeholder' => ts('- Select Case Type -'),
        'api' => [
          'params' => [
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
          'multiple' => TRUE,
        ],
      ],
      self::EVENTS => [
        'entity' => 'event',
        'placeholder' => ts('- Select Event Name -'),
        'api' => [
          'params' => [
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
        ],
      ],
      self::MEMBERSHIPS => [
        'entity' => 'membershipType',
        'placeholder' => ts('- Select Membership Type -'),
        'api' => [
          'params' => [
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
        ],
      ],
    ]);
  }

  /**
   * Return the fileds to populate the entity status reference field
   * for each supported entities in the certificate configure form
   *
   * @return string - json encoded array of the entity status field options
   */
  public static function getEntityStatusRefs() {
    return json_encode([
      self::CASES => [
        'placeholder' => ts('- Select Case Status  -'),
        'entity' => 'OptionValue',
        'api' => [
          'params' => [
            'option_group_id' => "case_status",
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
          'multiple' => TRUE,
        ],
      ],
      self::EVENTS => [
        'entity' => 'ParticipantStatusType',
        'placeholder' => ts('- Select Participant Status -'),
        'api' => [
          'params' => [
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
          'multiple' => TRUE,
        ],
      ],
      self::MEMBERSHIPS => [
        'entity' => 'membershipStatus',
        'placeholder' => ts('- Select Membership Status -'),
        'api' => [
          'params' => [
            'is_active' => 1,
          ],
        ],
        'select' => [
          'minimumInputLength' => 0,
          'multiple' => TRUE,
        ],
      ],
    ]);
  }

}
