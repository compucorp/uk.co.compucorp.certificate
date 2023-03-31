<?php

require_once 'certificate.civix.php';

use CRM_Certificate_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function certificate_civicrm_config(&$config) {
  _certificate_civix_civicrm_config($config);
  _compucertificate_add_token_subscribers();
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function certificate_civicrm_install() {
  _certificate_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function certificate_civicrm_postInstall() {
  _certificate_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function certificate_civicrm_uninstall() {
  _certificate_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function certificate_civicrm_enable() {
  _certificate_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function certificate_civicrm_disable() {
  _certificate_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function certificate_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _certificate_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function certificate_civicrm_entityTypes(&$entityTypes) {
  _certificate_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function certificate_civicrm_navigationMenu(&$menu) {
  _certificate_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('Certificates'),
    'name' => 'compu-configure-certificate',
    'url' => 'civicrm/admin/certificates',
    'permission' => 'configure certificates',
  ));

  _certificate_civix_insert_navigation_menu($menu, 'Administer/Communications', array(
    'label' => E::ts('Image Formats (Certificates)'),
    'name' => 'compucertificate-configure-imageformats',
    'url' => 'civicrm/admin/certificates/imageFormats?reset=1',
    'permission' => 'configure certificates',
  ));
}

/**
 * Implements hook_civicrm_permission().
 *
 * Declare permissions used by the extension
 */
function certificate_civicrm_permission(&$permissions) {
  $permissions['configure certificates'] = [
    ts('CompuCertificate: configure certificates'),
    ts('User can configure which message templates can be downloaded as certificates.'),
  ];
}

/**
 * Subscribes to token evaluate events, this enables
 * each entity to resolve tokens with the appropraite value
 *
 */
function _compucertificate_add_token_subscribers() {
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Case());
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Event());
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Contact());
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Participant());
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Membership());
  Civi::dispatcher()->addSubscriber(new CRM_Certificate_Token_Certificate());
}

function _compucertificate_getCaseIdFromUrlIfExist() {
  $caseId = NULL;
  if (!empty($_GET['caseid'])) {
    $caseId = (int) $_GET['caseid'];
  }

  return $caseId;
}

/**
 * Implements hook_civicrm_tokens().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_tokens
 */
function certificate_civicrm_tokens(&$tokens) {
  $tokens[CRM_Certificate_Token_Case::TOKEN] = CRM_Certificate_Token_Case::prefixedEntityTokens();
  $tokens[CRM_Certificate_Token_Event::TOKEN] = CRM_Certificate_Token_Event::prefixedEntityTokens();
  $tokens[CRM_Certificate_Token_Contact::TOKEN] = CRM_Certificate_Token_Contact::prefixedEntityTokens();
  $tokens[CRM_Certificate_Token_Participant::TOKEN] = CRM_Certificate_Token_Participant::prefixedEntityTokens();
  $tokens[CRM_Certificate_Token_Membership::TOKEN] = CRM_Certificate_Token_Membership::prefixedEntityTokens();
  $tokens[CRM_Certificate_Token_Certificate::TOKEN] = CRM_Certificate_Token_Certificate::prefixedEntityTokens();

  if (_compucertificate_getCaseIdFromUrlIfExist()) {
    $tokens['certificate_url']['certificate_url.case'] = 'Case Certificate URL';
  }
}

/**
 * Implements hook_civicrm_tokenvalues().
 */
function certificate_civicrm_tokenValues(&$values, $cids, $job = NULL, $tokens = [], $context = NULL) {
  $caseId = _compucertificate_getCaseIdFromUrlIfExist();

  $hooks = [new CRM_Certificate_Hook_Token_CaseCertificateUrlTokensValues($caseId)];

  foreach ($hooks as &$hook) {
    $hook->run($values, $cids, $job, $tokens, $context);
  }
}

/**
 * Implements addCiviCaseDependentAngularModules().
 */
function certificate_addCiviCaseDependentAngularModules(&$dependentModules) {
  $dependentModules[] = "certificate";
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function certificate_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Case' & $apiRequest['action'] === 'getdetails') {
    $wrappers[] = new CRM_Certificate_Api_Wrapper_Case();
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function certificate_civicrm_pageRun(&$page) {
  $hooks = [
    new CRM_Certificate_Hook_PageRun_EventPageTab($page),
    new CRM_Certificate_Hook_PageRun_MemberPageTab($page),
  ];

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

/**
 * Implements hook_civicrm_buildForm().
 */
function certificate_civicrm_buildForm($formName, &$form) {
  $hooks = [];
  if (CRM_Certificate_Hook_BuildForm_MessageTemplates::shouldRun($form)) {
    $hooks[] = new CRM_Certificate_Hook_BuildForm_MessageTemplates($form);
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}

/**
 * Implements hook_civicrm_postProcess().
 */
function certificate_civicrm_postProcess($formName, &$form) {
  $hooks = [];
  if (CRM_Certificate_Hook_PostProcess_MessageTemplates::shouldRun($form)) {
    $hooks[] = new CRM_Certificate_Hook_PostProcess_MessageTemplates($formName, $form);
  }

  array_walk($hooks, function ($hook) {
    $hook->run();
  });
}
