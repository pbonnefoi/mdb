(function($) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      $("path, circle").hover(function(e) {
        $('#info-box').css('display','block');
        $('#info-box').html($(this).data('info'));
      });

      $("path, circle").mouseleave(function(e) {
        $('#info-box').css('display','none');
      });

      $(document).mousemove(function(e) {
        $('#info-box').css('top',e.pageY-$('#info-box').height()-200);
        $('#info-box').css('left',e.pageX-($('#info-box').width()));
      }).mouseover();

      var ios = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
      if(ios) {
        $('a').on('click touchend', function() {
          var link = $(this).attr('href');
          window.open(link,'_blank');
          return false;
        });
      }
    }
  };
})(jQuery);
