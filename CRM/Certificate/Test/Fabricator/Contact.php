<?php

use Civi\Api4\Address;

/**
 * Fabricates contacts using API calls.
 */
class CRM_Certificate_Test_Fabricator_Contact {

  private static $defaultParams = [
    'contact_type' => 'Individual',
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'sequential'   => 1,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);
    $params['display_name'] = "{$params['first_name']} {$params['last_name']}";
    $result = civicrm_api3(
      'Contact',
      'create',
      $params
    );
    return array_shift($result['values']);
  }

  public static function fabricateWithAddress($params = []) {
    $params = array_merge(self::$defaultParams, $params);
    $params['display_name'] = "{$params['first_name']} {$params['last_name']}";
    $result = civicrm_api3(
      'Contact',
      'create',
      $params
    );
    $contact = array_shift($result['values']);

    $contact['address'] = Address::save(FALSE)
      ->addRecord([
        "contact_id" => $contact['id'],
        "location_type_id" => 5,
        "is_primary" => TRUE,
        "is_billing" => TRUE,
        "street_address" => "Coldharbour Ln",
        "street_number" => "42",
        "supplemental_address_1" => "Supplementary Address 1",
        "supplemental_address_2" => "Supplementary Address 2",
        "supplemental_address_3" => "Supplementary Address 3",
        "city" => "Hayes",
        "postal_code" => "UB3 3EA",
        "country_id" => 1226,
        "manual_geo_code" => FALSE,
        "timezone" => NULL,
        "name" => NULL,
        "master_id" => NULL,
      ])
      ->execute()
      ->first();

    return $contact;
  }

}
