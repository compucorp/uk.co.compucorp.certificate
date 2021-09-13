<?php

use Civi\Api4\MessageTemplate;
use Civi\Token\TokenProcessor;

class CRM_Certificate_Service_CertificateGenerator {

  /**
   * Converts the message template to html and resolve tokens
   * for the contact and entity
   *
   * @param int $templateId
   * @param int $contactId
   * @param int $entityId
   *
   * @return array
   */
  public function generate($templateId, $contactId, $entityId) {
    $content = $this->loadTemplate($templateId);
    $generatedTemplate = $this->renderMessageTemplate($content, $contactId, $entityId);
    return $generatedTemplate;
  }

  /**
   * Loads the message template
   *
   * @return array
   * @throws \API_Exception
   * @throws \CRM_Core_Exception
   */
  private function loadTemplate($templateId) {
    $apiCall = MessageTemplate::get(FALSE)
      ->addSelect('msg_subject', 'msg_text', 'msg_html', 'pdf_format_id', 'id')
      ->addWhere('id', '=', $templateId);
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
   * @param int $contactId
   * @param int $entityTypeId
   *
   * @return array
   */
  private function renderMessageTemplate(array $content, $contactId, $entityTypeId) {
    CRM_Core_Smarty::singleton()->pushScope([]);
    $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), ['smarty' => !TRUE]);
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

}
