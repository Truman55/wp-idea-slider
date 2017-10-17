jQuery.noConflict();
(function ( $ ) {
  $(document).ready(function() {
    var form = $('#wis_form');
    form.on('submit', ajaxUploadFiles);
  });


  function ajaxUploadFiles(e) {
    e.stopPropagation();
    e.preventDefault();
    var loader = $('.loader-wrapper');

    loader.css('display', 'flex');
    var fd = new FormData($('#wis_form'));
    fd.append('photo', $('#photo')[0].files[0]);
    fd.append('action', 'wis_load_image');

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,

      success:function (response) {
        var $html = $(response.html);
        $html.appendTo($('.wis_slider_list ul'))

        loader.fadeOut();
      },
      error: function (e) {
        alert(e.responseJSON.data.error);
      }
    })
  }
})(jQuery);