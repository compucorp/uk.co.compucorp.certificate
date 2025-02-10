{crmScript ext='uk.co.compucorp.certificate' file='js/vendor/html2canvas.min.js'}

<div id="compu-certificate-content" style="display: inline-block;">
  {$certificateContent}
</div>

<script language="javascript" type="text/javascript">
  let format = {$imageFormat}

  { literal }
  CRM.$(function ($) {
   format = format || {}
   const container = document.getElementById("compu-certificate-content");
   let extension = format.extension || 'jpg';
   let quality = Math.max(format.quality || 10, 10);
   const width = format.width || container.offsetWidth;
   const height = format.height || container.offsetHeight;
   const imageTypes = {jpg: 'image/jpeg', png: 'image/png'};

   html2canvas(container).then(function(canvas) {
      const extraCanvas = document.createElement("canvas");
      extraCanvas.setAttribute('width',width);
      extraCanvas.setAttribute('height',height);
      const ctx = extraCanvas.getContext('2d');
      ctx.drawImage(canvas,0,0,canvas.width, canvas.height,0,0,width,height);
      const dataURL = extraCanvas.toDataURL(imageTypes[`${extension}`], quality / 10);

      const img = new Image();
      img.src = dataURL
      document.body.innerHTML = "";
      document.body.appendChild(img);

      var a = document.createElement("a");
      a.download = "certificate."+extension;
      a.href = dataURL;
      a.click();
    });
  });
  { /literal}
</script>
