<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// \https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules/n

use CRM_Civicase_Helper_GlobRecursive as GlobRecursive;

/**
 * Get a list of JS files.
 */
function get_certificate_js_files() {
  return array_merge(
    ['ang/certificate.js'],
    GlobRecursive::getRelativeToExtension(
      'uk.co.compucorp.certificate',
      'ang/certificate/*.js'
    )
  );
}

return [
  'js' => get_certificate_js_files(),
  'basePages' => [],
];
