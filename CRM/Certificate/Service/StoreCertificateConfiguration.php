<?php

class CRM_Certificate_Service_StoreCertificateConfiguration {

    /**
     * @var array
     */
    public $values;

    /**
     * @param array $values
     *  Configuration values
     */
    public function __construct($values) {
        $this->values = $values;
    }
    
    /**
     * Create a new certificate configuration
     * 
     * @return array
     *  New Certificate configuration values
     */
    public function store() {
        $result = NULL;
        $values = $this->values;

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