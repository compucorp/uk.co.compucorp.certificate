
{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Certificate/Form/CertificateImageFormats.tpl"}
{else}
<div class="crm-content-block crm-block">
{if $rows}
    <div id="ltype">
        {strip}
        <table id="certificateImageFormats" class="row-highlight">
        <thead>
        <tr class="columnheader">
            <th>{ts}Name{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th >{ts}Default?{/ts}</th>
            <th ></th>
        </tr>
        </thead>
        {foreach from=$rows item=row}
        <tr id="row_{$row.id}" class="crm-certificate-image-format {cycle values="odd-row,even-row"} {$row.class}">
            <td class="crm-certificate-image-format-name">{$row.name}</td>
            <td class="crm-certificate-image-format-description">{$row.description}</td>
            <td class="crm-certificate-image-format-is_default">{icon condition=$row.is_default}{ts}Default{/ts}{/icon}&nbsp;</td>
          <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}
    </div>
{else}
    <div class="messages status no-popup">
      {icon icon="fa-info-circle"}{/icon}
      {ts}None found.{/ts}
    </div>
{/if}
    <div class="spacer"></div>
    <div class="action-link">
      {crmButton q="action=add&reset=1" id="newImageFormat"  icon="plus-circle"}{ts}Add Image Format{/ts}{/crmButton}
    </div>
{/if}
</div>
