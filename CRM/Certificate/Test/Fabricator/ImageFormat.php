<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

/**
 * Fabricates Certificate Image Format using API calls.
 */
class CRM_Certificate_Test_Fabricator_ImageFormat {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);

    if ($result = self::existingFormat($params)) {
      return $result;
    }
    $result = civicrm_api3(
      'OptionValue',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $name = md5(mt_rand());

    return [
      'option_group_id' => CompuCertificateImageFormat::NAME,
      'label' => $name,
      'name' => $name,
      'height' => '',
      'width' => '',
      'value' => '{"extension":"jpg","quality":5,"height":2,"width":12.55}',
    ];
  }

  public static function existingFormat(array $params) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => $params['option_group_id'],
      'value' => $params['value'],
    ]);

    return $result['values'][0] ?? FALSE;
  }

}
