CRM.$(function ($) {
  waitForElement($, 'div.crm-content-block.crm-membership-view-form-block', function ($, elements) {
    $('.ui-dialog-buttonpane > .ui-dialog-buttonset').append(downloadLink());
  });
});
