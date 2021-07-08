<?php

class CRM_Certificate_Service_StoreCertificateConfiguration {
    
    /**
     * Create a new certificate configuration
     * 
     * @param array $values
     *  Configuration values
     * @return array
     *  New Certificate configuration values
     */
    public static function store($values) {
        $result = NULL;

        CRM_Core_Transaction::create()->run(function($tx) use (&$result, $values) {
            $params['name'] = $values['certificate_name'];
            $params['entity'] = $values['certificate_type'];
            $params['template_id'] = $values['certificate_msg_template'];
            $statuses = explode(',', $values['certificate_status']);
            $entityTypes = explode(',', $values['certificate_linked_to']);

            $result['certificate'] = CRM_Certificate_BAO_CompuCertificate::create($params);
            $result['statuses'] = CRM_Certificate_BAO_CompuCertificateStatus::createStatuses($result['certificate']->id, $statuses);
            $result['entityTypes'] = CRM_Certificate_BAO_CompuCertificateEntityType::createEntityTypes($result['certificate']->id, $entityTypes);
        });

        return $result;
    }
}