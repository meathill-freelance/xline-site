$('#share-weixin').on('click', function () {
  var img = document.createElement('img')
    , url = location.href;
  img.src = 'http://s.jiathis.com/qrcode.php?url=' + url;
  img.className = 'hide';
  img.onload = function () {
    this.onload = null;
    this.className = '';
    $('#share-to-weixin').find('.qrcode').show()
      .end().find('.loading').hide();
  };
  $('#share-to-weixin').modal('show')
    .find('.loading').show()
    .end().find('.qrcode').html(img);

});