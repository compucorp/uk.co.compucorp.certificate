{crmStyle ext=uk.co.compucorp.certificate file=css/style.css}

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

  let ref = { $entityRefs }
  let statusRef = { $entityStatusRefs }
  let performingUpdate = false
  const TYPE_CASES = "1";
  const TYPE_EVENTS = "2";
  const FORMAT_IMAGE = "2";
  const TYPE_TEMPLATE = "1";

  { literal }

  let toggleRequiredMarker = ($, val) => {
    if (val === TYPE_CASES) {
      $('.participant_type_id').hide()
    } else if (val === TYPE_EVENTS) {
      if (!$('.participant_type_id > label > span.crm-marker').length) {
        $('.participant_type_id > label ').append('<span class="crm-marker" title="This field is required."> *</span>');
      }
      $('.participant_type_id').show()
    }
    else {
      $('.participant_type_id').hide()
    }
  }
  
  CRM.$(function ($) {

    $('.participant_type_id').hide();

    /**
     * if an entity is selected we want to populate the 
     * linked_to (entity type) and status (entity status) entity reference field
     * with the right values
     */
    CRM.$('[name=type]').on('change', function (e) {
      if (e.target.value > 0) {

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
              params: {active: true, option_group_id: 'participant_role'}
            },
            select: {
              minimumInputLength: 0
            }
          })
      }
    });

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

  { /literal}
</script>
