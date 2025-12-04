{crmStyle ext='uk.co.compucorp.certificate' file='css/style.css'}

<div id="bootstrap-theme">
  <div class="panel panel-default certificate__create-form-panel">
    <div class=" panel-body">
    <div class="form-hoizontal">
      {foreach from=$elementNames item=elementName}
      <div class="form-group row {$elementName}">
        <div class="col-sm-2 control-label">
          {$form.$elementName.label}
          {if in_array($elementName, $help)}
            {help id="$elementName" file="CRM/Certificate/Form/CertificateConfigure.hlp"}
          {/if}
        </div>
        <div class="col-sm-7 col-md-5">
          {$form.$elementName.html}
        </div>
      </div>
      {/foreach}
    </div>
  </div>

  <div class="crm-submit-buttons panel-footer">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
</div>

<script language="javascript" type="text/javascript">

  let ref = {$entityRefs}
  let statusRef = {$entityStatusRefs}
  let performingUpdate = false
  const TYPE_CASES = "1";
  const TYPE_EVENTS = "2";
  const TYPE_MEMBERSHIP = "3";
  const FORMAT_IMAGE = "2";
  const TYPE_TEMPLATE = "1";
  const previousFileURL = {$previousFile}

  {literal}


  let toggleRequiredMarker = ($, val) => {
    if (val === TYPE_CASES) {
      $('.participant_type_id').hide()
      $('.event_type_ids').hide()
    } else if (val === TYPE_EVENTS) {
      if (!$('.participant_type_id > label > span.crm-marker').length) {
        $('.participant_type_id > label ').append('<span class="crm-marker" title="This field is required."> *</span>');
      }
      $('.participant_type_id').show()
      $('.event_type_ids').show()
    }
    else {
      $('.participant_type_id').hide()
      $('.event_type_ids').hide()
    }
  }

  let toggleValidityDateFields = ($, val) => {
    if (val === TYPE_MEMBERSHIP) {
      $('.row.min_valid_from_date').show();
      $('.row.max_valid_through_date').show();
    } else {
      $('.row.min_valid_from_date').hide();
      $('.row.max_valid_through_date').hide();
    }
  }

  CRM.$(function ($) {

    $('.participant_type_id').hide();
    $('.event_type_ids').hide();

    if (previousFileURL && previousFileURL.length > 0) {
      // Create the anchor element
      const fileLink = $('<a>')
          .attr('href', previousFileURL)   // Set the href attribute to the file URL
          .text('Uploaded File')           // Text for the link
          .attr('target', '_blank');       // Open in a new tab

      // Append the anchor to the div with the specified class
      $('.row.download_file > .col-sm-7.col-md-5').append(fileLink);
    }

    toggleValidityDateFields($, CRM.$('[name=type]').val());

    /**
     * if an entity is selected we want to populate the
     * linked_to (entity type) and status (entity status) entity reference field
     * with the right values
     */
    CRM.$('[name=type]').on('change', function (e) {
      if (e.target.value > 0) {

        toggleValidityDateFields($, e.target.value);
        if (!performingUpdate) {
          $('[name=linked_to]').val('')
          $('[name=statuses]').val('')
        }

        performingUpdate = false;

        $('[name=linked_to]')
          .attr('placeholder', ref[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(ref[e.target.value])

        $('[name=statuses]')
          .attr('placeholder', statusRef[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(statusRef[e.target.value])

        if (e.target.value === TYPE_EVENTS) {
          $('[name=event_type_ids]')
            .attr('placeholder', '- Select -')
            .attr('disabled', false)
            .crmEntityRef({
              entity: 'OptionValue',
              api: {
                params: {
                  option_group_id: 'event_type',
                  is_active: 1,
                }
              },
              select: {
                multiple: true,
                minimumInputLength: 0
              }
            })
        }

        toggleRequiredMarker($, e.target.value);
      }
    });

    CRM.$('[name=linked_to]').on('change', function (e) {
      if (e.target.value > 0 && CRM.$('[name=type]').val() === TYPE_EVENTS) {
        $('[name=participant_type_id]')
          .attr('placeholder', '- Select Participant Type -')
          .attr('disabled', false)
          .crmEntityRef({
            entity: 'OptionValue',
            api: {
              description_field: null,
              params: {
                active: true,
                option_group_id: 'participant_role',
              }
            },
            select: {
              minimumInputLength: 0
            }
          })
      }
    });

    const showEventTypeWarning = () => {
      if (CRM.$('[name=type]').val() !== TYPE_EVENTS) {
        return;
      }

      const hasEvent = Boolean(CRM.$('[name=linked_to]').val());
      const hasEventType = Boolean(CRM.$('[name=event_type_ids]').val());

      if (hasEvent && hasEventType) {
        CRM.alert('Event and Event Type are both selected; the certificate will apply only when both match.', 'Notice', 'info', {expires: 5000, unique: 'certificate-event-type-warning'});
      }
    }

    $('[name=event_type_ids]').on('change', showEventTypeWarning);
    $('[name=linked_to]').on('change', showEventTypeWarning);

    CRM.$('[name=download_type]').on('change', function (e) {
      if (e.target.value === TYPE_TEMPLATE) {
        $('.download_file').hide();
        $('.download_format').show();
        $('.message_template_id').show();

        if (!$('.download_format label > span.crm-marker').length) {
          $('.download_format label ').append('<span class="crm-marker" title="This field is required."> *</span>');
        }
        if (!$('.message_template_id label > span.crm-marker').length) {
          $('.message_template_id label ').append('<span class="crm-marker" title="This field is required."> *</span>');
        }
      }else {
        $('.download_file').show();
        $('.download_format').hide();
        $('.message_template_id').hide();

        if (!$('.download_file label > span.crm-marker').length) {
          $('.download_file label ').append('<span class="crm-marker" title="This field is required."> *</span>');
        }
      }
    });


    //this is to trigger the entity ref, when value of certifcate type is set from the backend
    if ($('[name=type]')[0].value) {
      performingUpdate = true;
      $('[name=type]').change();
    }

    CRM.$('#download_format').change();
    CRM.$('[name=download_type]').change();
  });

  {/literal}
</script>
