<?php

use Civi\Api4\MessageTemplate;
use Civi\Token\TokenProcessor;

class CRM_Certificate_Service_CertificateDownload {

  /**
   * Gets the template associated with a certificate configuration and renders it
   * 
   * @param \CRM_Certificate_BAO_CompuCertificate $certificate
   * @param int $contactId
   * @param int $entityId 
   */
  public function download($certificate, $contactId, $entityId) {
    $content = $this->loadTemplate($certificate->template_id);
    $content = $this->renderMessageTemplate($content, $contactId, $entityId);
    $this->renderPDF($content);
  }

  /**
   * Load the specified template.
   *
   * @param int|null $messageTemplateID
   *
   * @return array
   * @throws \API_Exception
   * @throws \CRM_Core_Exception
   */
  private function loadTemplate($template_id) {
    $apiCall = MessageTemplate::get(FALSE)
      ->addSelect('msg_subject', 'msg_text', 'msg_html', 'pdf_format_id', 'id')
      ->addWhere('id', '=', $template_id);
    $messageTemplate = $apiCall->execute()->first();
    $content = [
      'subject' => $messageTemplate['msg_subject'],
      'text' => $messageTemplate['msg_text'],
      'html' => $messageTemplate['msg_html'],
      'format' => $messageTemplate['pdf_format_id'],
    ];
    CRM_Utils_Hook::alterMailContent($content);

    return $content;
  }

  /**
   * Render the message template, and resolve tokens.
   *
   * @param array $content
   * @param int $contactID
   * @param int $enttityTypeId
   *
   * @return array
   */
  private function renderMessageTemplate(array $content, $contactId, $entityTypeId): array {
    CRM_Core_Smarty::singleton()->pushScope([]);
    $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), ['smarty' => !true]);
    $tokenProcessor->addMessage('html', $content['html'], 'text/html');
    $tokenProcessor->addMessage('text', $content['text'], 'text/plain');
    $tokenProcessor->addMessage('subject', $content['subject'], 'text/plain');
    $tokenProcessor->addRow(['contactId' => $contactId, 'entityId' => $entityTypeId]);
    $tokenProcessor->evaluate();
    foreach ($tokenProcessor->getRows() as $row) {
      $content['html'] = $row->render('html');
      $content['text'] = $row->render('text');
      $content['subject'] = $row->render('subject');
    }
    CRM_Core_Smarty::singleton()->popScope();
    $content['subject'] = trim(preg_replace('/[\r\n]+/', ' ', $content['subject']));
    return $content;
  }

  /**
   * Converts html content to PDF, and return PDF file to the browser
   * 
   * @param array $content
   */
  private function renderPDF(array $content) {
    ob_end_clean();
    CRM_Utils_PDF_Utils::html2pdf(
      nl2br($content['html']),
      'certificate.pdf',
      FALSE,
      ['orientation' => 'landscape']
    );
    CRM_Utils_System::civiExit();
  }
}
