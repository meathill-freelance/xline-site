/**
 * Created by 路佳 on 14-9-2.
 */
;(function (ns) {
  'use strict';

  var API = '/wp-content/themes/xline/api/index.php'
    , Collection = Backbone.Collection.extend({
      url: API
    })
    , Model = Backbone.Model.extend({
      defaults: {
        'number': 9,
        'name': '',
        'size': 1
      },
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
      this.modal = new ns.EditModal({
        el: '#edit-modal'
      });
      this.modal.on('save', this.modal_saveHandler, this);
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
      model.destroy({
        success: _.bind(this.model_destroySuccessHandler, this)
      });
    },
    edit_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , attr = button.data()
        , title = button.attr('title');
      this.editTarget = button;
      attr.prop = button.attr('href').substr(1);
      attr.value = button.text();
      attr.title = title;
      this.modal.load(attr);
    },
    modal_saveHandler: function () {
      var tr = this.editTarget.closest('tr')
        , id = tr.attr('id')
        , model = this.collection.get(id) || new Model({id: id})
        , attr = this.modal.attr;
      this.editTarget.text(this.modal.getLabel());
      this.modal.hide();
      model.set(attr.prop, this.modal.getValue());
      if (!model.collection) {
        this.collection.add(model, {silent: true});
      }
    },
    model_destroySuccessHandler: function (model) {
      this.$('#' + model.has('id') ? model.id : model.cid).fadeOut(function () {
        $(this).remove();
      });
    },
    model_saveSuccessHandler: function () {
      this.$('.save-button.processing')
        .removeClass('processing')
        .prop('disabled', false)
        .find('i').removeClass('fa-spin fa-spinner')
        .addClass('fa-check')
        .end().siblings()
          .prop('disabled', false);
    },
    newRowButton_clickHandler: function (event) {
      var button = $(event.currentTarget)
        , design = button.closest('.design')
        , id = Number(design.attr('id').match(/design\-(\d+)/)[1])
        , keys = design.data('keys');
      this.collection.add({
        design: id,
        keys: keys
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
        , model = this.collection.get(id);
      if (model) {
        model.save(null, {
          patch: true,
          success: _.bind(this.model_saveSuccessHandler, this)
        });
        button.addClass('processing')
          .find('i').removeClass('fa-check')
          .addClass('fa-spin fa-spinner');
        button.add(button.siblings()).prop('disabled', true);
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