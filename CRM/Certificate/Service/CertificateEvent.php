<?php

use CRM_Certificate_BAO_CompuCertificateEventAttribute as CertificateEventAttribute;

class CRM_Certificate_Service_CertificateEvent extends CRM_Certificate_Service_Certificate {

  /**
   * {@inheritDoc}
   */
  protected function addOptionsCondition(&$query, $values) {
    $query->join('cert_event_attr', 'LEFT JOIN `' . CertificateEventAttribute::getTableName() . '` cert_event_attr ON (cert_event_attr.certificate_id = ccc.id)');

    $participantTypeCondition = "cert_event_attr.participant_type_id IS NULL";

    if (!empty($values['participant_type_id'])) {
      $attrValues = sprintf('(%s)', implode(',', (array) $values['participant_type_id']));
      $participantTypeCondition = "($participantTypeCondition OR cert_event_attr.participant_type_id IN $attrValues)";
    }

    $linkedToCondition = $this->linkedToCondition($values['linked_to']);
    $statusesCondition = $this->statusesCondition($values['statuses']);

    $query = $query->where($linkedToCondition);

    if (empty($values['participant_type_id']) && empty($values['statuses'])) {
      return;
    }

    // This is to avoid event entity from having multiple certificate configuration.
    $conjuction = empty($values['participant_type_id']) || empty($values['statuses']) ? ' OR ' : ' AND ';
    $query = $query->where(implode($conjuction, [$statusesCondition, $participantTypeCondition]));
  }

  /**
   * {@inheritDoc}
   */
  protected function storeExtraValues(&$result, $values) {
    $participantTypeId = $values['participant_type_id'];
    $certificateId = $result['certificate']->id;
    $result['eventAttribute'] = CertificateEventAttribute::assignCertificateEventAttribute($certificateId, $participantTypeId);
  }

}
