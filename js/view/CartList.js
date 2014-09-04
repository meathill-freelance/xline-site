/**
 * Created by 路佳 on 14-9-2.
 */
;(function (ns) {
  'use strict';

  var API = '/wp-content/themes/xline/api/'
    , Collection = Backbone.Collection.extend({
      url: API
    })
    , Model = Backbone.Model.extend({
      urlRoot: API
    });

  ns.CartList = Backbone.View.extend({
    events: {
      'click .remove-design-button': 'removeDesignButton_clickHandler',
      'click .edit': 'edit_clickHandler',
      'click .new-row-button': 'newRowButton_clickHandler',
      'click .delete-button': 'deleteButton_clickHandler',
      'click .save-button': 'saveButton_clickHandler'
    },
    initialize: function () {
      this.template = Handlebars.compile($('#cart-row').html());
      this.modal = $('#edit-modal');
      this.model.on('click', '.btn-primary', _.bind(this.modal_saveHandler, this));
      this.collection = new Collection();
      this.on('add', this.collection_addHandler, this);
    },
    collection_addHandler: function (model) {
      var tr = $(this.template());
      tr.attr('id', model.cid);
      this.$('.design-' + model.get('design')).find('.member-list')
        .append(tr);
    },
    deleteButton_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , tr = button.closest('tr')
        , id = tr.attr('id')
        , model = this.collection.get('id') || new Backbone.Modal({id: id});
      model.destroy();
    },
    edit_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , type = button.data('type') ? button.data('type') : 'input'
        , init = {
          prop: button.attr('href').substr(1),
          value: button.text()
        }
        , self = this;
      this.editTarget = button;
      if (type === 'select') {
        init.options = $(button.data('options')).html();
      }
      $.get('/wp-content/themes/xline/template/edit/' + type + '.hbs', function (response) {
        var template = Handlebars.compile(response);
        self.modal.modal('show')
          .find('.modal-body').html(template(init));
      });
    },
    modal_saveHandler: function (event) {
      var value = this.model.find('[name=prop]').val()
        , tr = this.editTarget.closest('tr')
        , id = tr.attr('id')
        , model = this.collection.get(id) || new Model({id: id})
        , prop = this.editTarget.attr('href').substr(1);
      this.editTarget.innerText = value;
      model.set(prop, value);
    },
    newRowButton_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , design = button.closest('.design')
        , id = Number(design.attr('id').match(/design\-(\d+)/)[1]);
      this.collection.add({
        design: id
      });
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
    saveButton_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , tr = button.closest('tr')
        , id = tr.attr('id')
        , model = this.collection.get('id');
      if (model) {
        model.save();
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