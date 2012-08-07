jQuery.noConflict();

jQuery(document).ready(function($){

  var $page = $('#inner-wrapper');

  // ------------------------------
  // Form Input Placeholder
  // ------------------------------
  if (!Modernizr.inputtypes.email)
  {
    $('input[placeholder], textarea[placeholder]').each(function(i, input){
      var $input = $(input);

      // Initially load the placeholder value
      if ($input.val() == '') { $input.val($input.attr('placeholder')); }

      $input
        .bind('focusin', function(){
          var $this = $(this);
          if ($this.val() == $input.attr('placeholder')) { $this.val(''); }
        })
        .bind('focusout', function(){
          var $this = $(this);
          if ($this.val() == '') { $this.val($input.attr('placeholder')); }
        });
    });
  }

  // Make icons inside single input forms
  // clickable to submit the form
  $page.delegate('.icon-arrow-in-blue-circle, .icon-magnifying-glass', 'click', function(e){
    e.preventDefault();
    $(e.currentTarget).closest('form').submit();
  });

  $page.delegate('.toggle-expanding-content', 'click', function(e){
    e.preventDefault();
    $($(e.currentTarget).attr('href')).toggleClass('collapsed expanded');
  });

  $('.slideshow').carousel({
    paginationPosition: 'inside',
    btnsPosition: 'inside',
    pagination: true,
    loop: true,
    animSpeed: 1600,
    autoSlide: true,
    autoSlideInterval: 4500
  });


  // Show messages drawer after page load
  // if the site has messages to show
  var $drawer = $('ul.messages')

  if ($drawer.is('*')) {
    $drawer.slideToggle(300).delay(4000).slideToggle(300);
  }

});
