/**
 * 这个js主要实现跟购买相关的操作
 */
$(function () {
  function reset(button) {
    button.prop('disabled', false)
      .find('.fa-spin')
        .removeClass('fa-spin fa-spinner')
        .addClass('fa-shopping-cart');
  }
  $('.add-to-cart').on('submit', function (event) {
    var id = this.elements.id.value
      , pid = this.elements.pid.value
      , button = $(this).find('button');
    button
      .prop('disabled', true)
      .find('.fa-shopping-cart')
        .removeClass('fa-shopping-cart')
        .addClass('fa-spin fa-spinner');
    $.ajax(this.action, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'line_buy',
        id: id,
        pid: pid
      },
      success: function (response) {
        $('#msg')
          .html(response.msg + '。<a href="/cart">点击查看</a>')
          .addClass('alert-success')
          .slideDown();
        reset(button);
      },
      error: function (xhr, status, error) {
        console.log(xhr, status, error);
        $('#msg')
          .text('添加失败，请稍后重试')
          .addClass('alert-danger')
          .slideDown();
        reset(button);
      }
    });
    event.preventDefault();
    return false;
  });
});
