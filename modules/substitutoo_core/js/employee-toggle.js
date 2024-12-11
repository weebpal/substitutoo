(function ($, Drupal) {
  Drupal.behaviors.toggleContentBehavior = {
    attach: function (context, settings) {
      $(once('toggleEmployee', '.manage-employee-form', context)).each(function () {
        console.log(11)
        $('.toggle-button').on('click', function () {
          console.log(1111)
          var targetClass = $(this).data('target');
          $(targetClass).toggle(); 
          $(this).closest('div').toggleClass('toggle-hide')
        })
      });
    }
  };
})(jQuery, Drupal);

