<div id="image_format" class="crm-accordion-wrapper crm-html_email-accordion ">
  <div class="crm-accordion-header">
    {$form.image_format_id.label}
  </div><!-- /.crm-accordion-header -->
  <div class="crm-accordion-body">
    <div class="spacer"></div>
    <div class='html'>
      {$form.image_format_id.html}
      {help id="id-image-format" file="CRM/Admin/Form/MessageTemplates/ImageFormats.hlp"}
      <div class="description">{ts}Image format to use when downloading certificates using this template.{/ts}</div>
    </div>
  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<script language="javascript" type="text/javascript">
  { literal }
  CRM.$(function ($) {
    $("#image_format").insertAfter("#pdf_format");
  });
  { /literal}
</script>