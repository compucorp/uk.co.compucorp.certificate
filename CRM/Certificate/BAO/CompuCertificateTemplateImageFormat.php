<?php

class CRM_Certificate_BAO_CompuCertificateTemplateImageFormat extends CRM_Certificate_DAO_CompuCertificateTemplateImageFormat {

  /**
   * Create a new CompuCertificateTemplateImageFormat based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Certificate_DAO_CompuCertificateTemplateImageFormat|NULL
   *
   */
  public static function create($params) {
    $className = 'CRM_Certificate_DAO_CompuCertificateTemplateImageFormat';
    $entityName = 'CompuCertificateTemplateImageFormat';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Update or create new CompuCertificateTemplateImageFormat.
   *
   * @param array $params key-value pairs
   */
  public static function upsert($params) {
    $templateImageFormat = self::getByTemplateId($params['template_id']);

    if (!empty($templateImageFormat)) {
      $params['id'] = $templateImageFormat->id;
    }

    return self::create($params);
  }

  /**
   * Returns a CompuCertificateTemplateImageFormat or NULL
   *
   * @param int $templateId
   */
  public static function getByTemplateId($templateId) {
    $templateImageFormat = new self();
    $templateImageFormat->whereAdd('template_id = ' . $templateId);
    $count = $templateImageFormat->find(TRUE);

    return $count > 0 ? $templateImageFormat : NULL;
  }

}
