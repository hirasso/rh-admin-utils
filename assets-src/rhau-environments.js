
var jQuery = $ = global.jQuery;

import './scss/modules/environments.scss';

(function($){
  
  $(document).ready(function(){

    var $wrap = $(".rh-environment-links");

    if( !$wrap.length ) {
      return;
    }    

    var devRoot = $wrap.attr('data-dev-root');

    $(document).on('keydown', function(e) {
      if( $(e.target).filter('button, input, textarea, [contenteditable="true"]').length ) return;
      switch( e.keyCode ) {
        case 32: //space
          e.preventDefault();
          if( !$wrap.hasClass('is-visible') ) {
            $wrap.addClass('is-visible');
            $wrap.find('a:first').focus();
          } else {
            openEnvironmentLink($(e.target));
          }
          break;
        case 27: //esc
          $wrap.removeClass('is-visible');
          break;
        case 9: //tab
          if( $wrap.hasClass('is-visible') ) {
            traverseEnvironmentLinks(e.shiftKey ? 'previous' : 'next');
            e.preventDefault();
          }
          break;
      }
      
    });
    $(document).on('click', 'a.rh-environment-link', function(e) {
      e.preventDefault();
      openEnvironmentLink($(this));
    });
    $(document).on('mouseenter', 'a.rh-environment-link', function(e) {
      $(this).focus();
    });

    function openEnvironmentLink($el) {
      if( !$wrap.hasClass('is-visible') ) return;
      var link = window.location.href;
      var remoteRoot = $el.attr('data-remote-root');
      var re = new RegExp( devRoot , "gi");
      link = link.replace(re, remoteRoot);
      window.open( link );
      $wrap.removeClass('is-visible');
    }

    function traverseEnvironmentLinks(direction) {
      var $next;
      var $links = $('a.rh-environment-link');
      var $current = $links.filter(':focus');
      if( !$current.length ) {
        $links.first().focus();
        return;
      }
      switch( direction ) {
        case 'next':
          $next = $current.next('a');
          if( !$next.length ) $next = $links.first();
          $next.focus();
          break;
        case 'previous':
          $next = $current.prev('a');
          if( !$next.length ) $next = $links.last();
          $next.focus();
          break;
      }
      
    }

    
  });
  
})(jQuery);
