{* HEADER *}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

{foreach from=$elementNames item=elementName}
  <div class="crm-section">
    <div class="label">{$form.$elementName.label}</div>
    <div class="content">{$form.$elementName.html}</div>
    <div class="clear"></div>
  </div>
{/foreach}

{* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT) *}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script  language="javascript" type="text/javascript">

  let ref = {$entityRefs}
  let statusRef = {$entityStatusRefs}

  {literal}
  CRM.$(function($) {
    /**
     * if entity is selected we want to populate the 
     * linked_to (entity type) and certificate_status (entity status) entity reference field
     * with the right values
     */
    CRM.$('[name=certificate_type]').on('change', function (e) {
      if (e.target.value > 0) {
        $('[name=certificate_linked_to]')
          .attr('placeholder', ref[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(ref[e.target.value])

        $('[name=certificate_status]')
          .attr('placeholder', statusRef[e.target.value]['placeholder'])
          .attr('disabled', false)
          .crmEntityRef(statusRef[e.target.value])
      }
    })

    //this is to trigger the entity ref, when value of certifcate type is set from the backend
    if ($('[name=certificate_type]')[0].value) {
      $('[name=certificate_type]').change();
    }
  });
  {/literal}
</script>
