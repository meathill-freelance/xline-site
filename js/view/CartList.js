/**
 * Created by 路佳 on 14-9-2.
 */
;(function (ns) {
  ns.CartList = Backbone.View.extend({
    events: {
      'click .remove-design-button': 'removeDesignButton_clickHandler',
      'click .edit': 'edit_clickHandler',
      'click .new-row-button': 'newRowButton_clickHandler'
    },
    initialize: function () {
      this.template = Handlebars.compile($('#cart-row').html());
      this.modal = $('#edit-modal');
    },
    edit_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , type = button.data('type') ? button.data('type') : 'input'
        , value =button.text()
        , self = this;
      $.get('/wp-content/themes/xline/template/edit/' + type + '.hbs', function (response) {
        var template = Handlebars.compile(response);
        self.modal.modal('show')
          .find('.modal-body').html(template({value: value}));
      });
    },
    newRowButton_clickHandler: function (event) {
      var button = $(event.currentTarget);
      button.closest('tfoot').prev().append(this.template());
    },
    removeDesignButton_clickHandler: function (event) {
      if (confirm('这将会把该设计所有服装从购物车中移除，您确定么？')) {
        var button = $(event.currentTarget)
          , href = button.attr('href')
          , url = href.substr(0, href.indexOf('?'))
          , params = href.substr(href.indexOf('?') + 1)
          , arr = params.split('&')
          , data = {
            action: 'line_remove_design'
          };
        for (var i = 0, len = arr.length; i < len; i++) {
          var kv = arr.split('=');
          data[kv[0]] = kv[1];
        }
        $.ajax(url, {
          data: data,
          dataType: 'json',
          context: this,
          success: this.removeDesign_successHandler,
          error: this.removeDesign_errorHandler
        });
      }
    },
    removeDesign_successHandler: function (response) {
      this.$('#' + response.design).fadeOut(function () {
        $(this).remove();
      });
    },
    removeDesign_errorHandler: function () {
      alert('移除失败');
    }
  });
}(xline.view));