/**
 * Created by 路佳 on 14-9-2.
 */
;(function (ns) {
  ns.LoginForm = Backbone.View.extend({
    events: {
      'submit': 'submitHandler'
    },
    initialize: function () {
      this.submit = $('[form=login-form]');
    },
    showResult: function (isSuccess, msg) {
      var classes = isSuccess ? 'alert-success fa-smile-o' : 'alert-danger fa-frown-o';
      this.submit.prop('disabled', false)
        .find('i').toggleClass('fa-check fa-spin fa-spinner');
      this.$('.alert')
        .addClass('fa ' + classes)
        .html(msg)
        .slideDown();
    },
    submitHandler: function (event) {
      this.submit.prop('disabled', true)
        .find('i').toggleClass('fa-check fa-spin fa-spinner');
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: this.el.action,
        context: this,
        data: {
          'action': 'ajax_login',
          'log': $('#username').val(),
          'pwd': $('#password').val(),
          'security': $('#security').val(),
          'rememberme': $('#remember-me').prop('checked') ? 'forever' : ''
        },
        success: this.success,
        error: this.error
      });
      event.preventDefault();
    },
    success: function (response) {
      if (response.code === 0) {
        $('.me .login').remove();
        $('.me .profile').removeClass('hide');
        this.showResult(true, '登录成功')
        var self = this;
        setTimeout(function () {
          self.$el.modal('hide');
        }, 3000);
      } else {
        this.showResult(false, response.msg || ' 登录失败');
      }
    },
    error: function (xhr, status, error) {
      this.showResult(false, xhr.responseJSON.msg || ' 登录失败');
    }
  });
}(xline.view));