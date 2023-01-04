<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

/**
 * Fabricates Certificate Image Format using API calls.
 */
class CRM_Certificate_Test_Fabricator_ImageFormat {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);

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
      'value' => '{"extension":"jpg","quality":5,"height":2,"width":12.55}',
    ];
  }

}
