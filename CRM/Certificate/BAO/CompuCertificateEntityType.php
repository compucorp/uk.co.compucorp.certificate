<?php
use CRM_Certificate_ExtensionUtil as E;

class CRM_Certificate_BAO_CompuCertificateEntityType extends CRM_Certificate_DAO_CompuCertificateEntityType {

  /**
   * Create a new CompuCertificateEntityType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateEntityType|NULL
   * 
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateEntityType';
    $entityName = 'CompuCertificateEntityType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
