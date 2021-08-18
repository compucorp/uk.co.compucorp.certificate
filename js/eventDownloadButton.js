var waitForElement = function ($, elementPath, callBack) {
  window.setTimeout(function(){
    if($(elementPath).length){
      callBack($, $(elementPath));
    }else{
      waitForElement($, elementPath, callBack);
    }
  },500)
}

var downloadLink = function () {
  let a = document.createElement('a');
  a.innerHTML = ts('Print Certificate')
  a.setAttribute('href', CRM.vars.certificate.download_url);
  a.setAttribute('target', '_blank')
  return a
}

var certificateDownloadRow = function () {
  let row = document.createElement('tr');
  row.classList.add('crm-event-participantview-form-block-event_source')
  let firstColumn = document.createElement('td')
  let secondColumn = document.createElement('td')
  
  firstColumn.innerHTML = "Certificates"
  firstColumn.classList.add('label')
  secondColumn.append(downloadLink())

  row.append(firstColumn)
  row.append(secondColumn)
  return row
}

CRM.$(function ($) {
  waitForElement($, 'div.crm-event-participant-view-form-block table.crm-info-panel', function ($, elements) {
    elements[0].querySelector('tbody').append(certificateDownloadRow())
  })
})