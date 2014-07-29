/**
 * Created by 路佳 on 14-6-17.
 */
$('.style-list')
  .on('click', 'label', function (event) {
    if (event.target.tagName.toLowerCase() === 'i') {
      return;
    }
    if ($(this).hasClass('disabled')) {
      event.preventDefault();
    }
    var className = /top/.test(this.className) ? 'top' : 'pants';
    $(this).addClass('active')
      .siblings('.' + className).removeClass('active');
  })
  .on('click', '.preview-button', function (event) {
    $('#popup')
      .modal('show')
      .find('.modal-body').html($(event.currentTarget).siblings('img').clone());
    event.stopPropagation();
    event.preventDefault();
  })
  .on('submit', function (event) {
    var cloth = [];
    $('.tab-pane.active input:checked').each(function () {
      cloth.push(this.value);
    })
    var flashvars = {
      cloth: cloth.join(',')
    };
    var params = {
      menu: "false",
      scale: "noScale",
      allowFullscreen: "true",
      allowScriptAccess: "always",
      bgcolor: "010101",
      wmode: "direct" // can cause issues with FP settings & webcam
    };
    var attributes = {
      id:"DIY"
    };
    $(this).hide();
    $('.diy-container').removeClass('hide');
    swfobject.embedSWF(
      "/wp-content/themes/line/swf/DIY.swf",
      "diy-flash", "100%", "100%", "11.0.0",
      "../swf/expressInstall.swf",
      flashvars, params, attributes);

    event.preventDefault();
    return false;
  });

function backToForm() {
  setTimeout(function () {
    if (confirm('重新选择款式之后，您需要从头进行设计，确定么？')) {
      $('#DIY').remove();
      $('.diy-container')
        .addClass('hide')
        .html('<div id="diy-flash"></div>');
      $('.style-list').show();
    }
  }, 10);

}