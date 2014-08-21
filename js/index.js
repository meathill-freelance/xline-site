/**
 * Created by 路佳 on 14-7-19.
 */
$(function () {
  // ajax login
  $('#login-form').on('submit', function (event) {
    var form = $(this);
    $('[form=login-form]').prop('disabled', true)
      .find('i').toggleClass('fa-check fa-spin fa-spinner');
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: this.action,
      data: {
        'action': 'ajax_login',
        'log': $('#username').val(),
        'pwd': $('#password').val(),
        'security': $('#security').val(),
        'rememberme': $('#remember-me').prop('checked') ? 'forever' : ''
      },
      success: function(response){
        if (_.isObject(response) && response.code === 0) {
          $('[form=login-form]').prop('disabled', false)
            .find('i').toggleClass('fa-check fa-spin fa-spinner');
          $('.me .login').remove();
          $('.me .profile').removeClass('hide');
          form.find('.alert')
            .addClass('alert-success fa fa-smile-o')
            .text(response.msg)
            .slideDown();
          setTimeout(function () {
            $('#login-modal').modal('hide');
          }, 3000);
        } else {
          $('[form=login-form]').prop('disabled', false)
            .find('i').toggleClass('fa-check fa-spin fa-spinner');
          form.find('.alert')
            .addClass('alert-danger fa fa-frown-o')
            .text(response.msg || ' 登录失败')
            .slideDown();
        }
      },
      error: function (xhr, status, error) {
        $('[form=login-form]').prop('disabled', false)
          .find('i').toggleClass('fa-check fa-spin fa-spinner');
        form.find('.alert')
          .addClass('alert-danger fa fa-frown-o')
          .html(xhr.responseJSON.msg || ' 登录失败')
          .slideDown();
      }
    });
    event.preventDefault();
  });
});