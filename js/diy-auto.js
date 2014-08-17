/**
 * Created by 路佳 on 14-8-18.
 */
var id = $('.flash-container').data('id');
if (id) {
  var url = '/wp-content/themes/xline/api/?id=' + id;
  showFlash([url, url].join(','));
}