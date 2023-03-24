<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Contact
 *
 * Generate "certificate_contact.*" tokens.
 *
 * This class defines custom contact tokens
 */
class CRM_Certificate_Token_Contact extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate contact';

  /**
   * Here we define list of the extra tokens we are adding
   */
  const customFields = [
    "employer_inline_address" => "Employer Inline Address",
  ];

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    return self::customFields;
  }

  /**
   * To perform a bulk lookup before rendering tokens
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   *
   * @return mixed
   */
  public function prefetch(TokenValueEvent $e) {
    $contactId = $e->getTokenProcessor()->getContextValues('contactId');

    $resolvedTokens = [];

    try {
      if (is_array($contactId)) {
        $contactId = $contactId[0];
        $contact = \Civi\Api4\Contact::get()
          ->addSelect('employer_id')
          ->addWhere('id', '=', $contactId)
          ->addChain('employerAddress', \Civi\Api4\Address::get()
            ->addSelect('street_address', 'supplemental_address_1', 'county_id:label', 'country_id:label', 'city')
            ->addWhere('contact_id', '=', '$employer_id')
            ->addWhere('is_primary', '=', TRUE)
          )
          ->execute()
          ->first();

        if (empty($contact)) {
          return $resolvedTokens;
        }

        $this->resolveFields($contact, $resolvedTokens);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Resolve the value of the custom contact token fields.
   *
   * @param array $contact
   * @param array &$resolvedTokens
   */
  private function resolveFields($contact, &$resolvedTokens) {
    if (!empty($contact["employerAddress"])) {
      $address = [
        $contact["employerAddress"][0]["street_address"],
        $contact["employerAddress"][0]["supplemental_address_1"],
        $contact["employerAddress"][0]["city"],
        $contact["employerAddress"][0]["county_id:label"],
        $contact["employerAddress"][0]["country_id:label"],
      ];

      $resolvedTokens['employer_inline_address'] = implode(", ", array_filter($address));
    }
  }

}
