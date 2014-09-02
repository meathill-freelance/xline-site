/**
 * Created by 路佳 on 14-9-3.
 */
;(function (ns) {
  var map = {
    '#login-form': ns.LoginForm,
    '#cart-list': ns.CartList
  }

  var manager = ns.Manager = {
    init: function () {
      for (var prop in map) {
        if ($(prop).length) {
          var component = new map[prop]({
            el: prop
          });
        }
      }
    }
  }
}(xline.view));