<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

class CRM_Certificate_Page_CertificateImageFormats extends CRM_Core_Page_Basic {

  private $title = 'Certificate (Image) Formats';

  public $useLivePageJS = TRUE;

  /**
   * The action links that we need to display for the browse screen.
   *
   * @var array
   */
  public static $_links = NULL;

  /**
   * Browse all PDF Page Formats.
   *
   * @param null $action
   */
  public function browse($action = NULL) {
    $imageFormatList = CompuCertificateImageFormat::getList();

    // Add action links to each of the Image Formats
    $action = array_sum(array_keys($this->links()));
    foreach ($imageFormatList as & $format) {
      $format['action'] = CRM_Core_Action::formLink(
        self::links(),
        $action,
        ['id' => $format['id']],
        ts('more'),
        FALSE,
        '',
        '',
        $format['id']
      );
    }

    $this->assign('rows', $imageFormatList);
  }

    /**
   * Get name of edit form.
   *
   * @return string
   *   Classname of edit form.
   */
  public function editForm() {
    return 'CRM_Certificate_Form_CertificateImageFormats';
  }

  /**
   * Get edit form name.
   *
   * @return string
   *   name of this page.
   */
  public function editName() {
    return $this->title;
  }

  /**
   * Get user context.
   *
   * @param null $mode
   *
   * @return string
   *   user context.
   */
  public function userContext($mode = NULL) {
    return 'civicrm/admin/certificates/imageFormats';
  }

  /**
   * Get BAO Name.
   *
   * @return string
   *   Classname of BAO.
   */
  public function getBAOName() {
    return 'CRM_Certificate_BAO_CompuCertificateImageFormat';
  }

  /**
   * Get action Links.
   *
   * @return array
   *   (reference) of action links
   */
  public function &links() {
    if (!(self::$_links)) {
      self::$_links = [
        CRM_Core_Action::UPDATE => [
          'name' => ts('Edit'),
          'url' => 'civicrm/admin/certificates/imageFormats',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Certificate Image Format'),
        ],
        CRM_Core_Action::DELETE => [
          'name' => ts('Delete'),
          'url' => 'civicrm/admin/certificates/imageFormats',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Certificate Image Format'),
        ],
      ];
    }

    return self::$_links;
  }

}
