<?php

use CRM_Certificate_DAO_CompuCertificateEventAttribute as CertificateEventAttribute;

class CRM_Certificate_Service_CertificateEvent extends CRM_Certificate_Service_Certificate {

  /**
   * {@inheritDoc}
   */
  protected function extraCondition(&$query, &$optionsCondition, $values, &$conjuction) {
    $query->join('cert_event_attr', 'LEFT JOIN `' . CertificateEventAttribute::$_tableName . '` cert_event_attr ON (cert_event_attr.certificate_id = ccc.id)');

    $optionsCondition[] = "cert_event_attr.participant_type_id IS NULL";

    if (!empty($values['participant_type_id'])) {
      $attrValues = sprintf('(%s)', implode(',', (array) $values['participant_type_id']));
      $optionsCondition[] = "cert_event_attr.participant_type_id IN $attrValues";
    }

    // This is to avoid an entity having multiple event certificate configuration,
    // i.e. in a case where a configuration that has participant_type_id 'all' and statuses for a specific status,
    // and the user attepmts to create another configuration with participant_type_id for a specific type and statuses for 'all',
    // then a ConfigurationExistException would be thrown.
    $conjuction = empty($values['participant_type_id']) || $conjuction === ' OR ' ? ' OR ' : ' AND ';
  }

  /**
   * {@inheritDoc}
   */
  protected function storeExtraValues(&$result, $values) {
    $values = [
      'certificate_id' => $result['certificate']->id,
      'participant_type_id' => (int) $values['participant_type_id'],
    ];

    $eventAttributeDAO = CRM_Certificate_BAO_CompuCertificateEventAttribute::create($values);
    $result['eventAttrbute'] = $eventAttributeDAO->toArray();
  }

}
