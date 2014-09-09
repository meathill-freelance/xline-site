/**
 * Created by meathill on 14-9-9.
 */
;(function (ns) {
  ns.EditModal = Backbone.View.extend({
    events: {
      'keydown input': 'input_keydownHandler',
      'click .btn-primary': 'confirmHandler'
    },
    getLabel: function () {
      return this.attr.modal === 'select' ? this.$('[name=prop]').find(':selected').text() : this.getValue();
    },
    getValue: function () {
      return this.$('[name=prop]').val();
    },
    hide: function () {
      this.$el.modal('hide');
    },
    load: function (attr) {
      this.attr = attr;
      var self = this
        , modal = attr.modal ? attr.modal : 'input';
      if (modal === 'select') {
        attr.options = $(attr.options).html();
      }
      $.get('/wp-content/themes/xline/template/edit/' + modal + '.hbs', function (response) {
        var template = Handlebars.compile(response);
        self.$('.modal-body').html(template(attr));
      });
      this.$el.modal('show')
        .find('.modal-body').html('<p align="center"><i class="fa fa-spin fa-spinner fa-2x"></i></p>');
    },
    confirmHandler: function () {
      this.trigger('save');
    },
    input_keydownHandler: function (event) {
      if (event.keyCode === 13) {
        this.trigger('save');
      }
    }
  });
}(xline.view));