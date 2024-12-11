(function ($, Drupal) {
  Drupal.behaviors.toggleAssignmentsBulk = {
    attach: function (context, settings) {
      console.log(111)
        $(once('toggleAssignmentsBulk', '.bulk_assign_employee_form', context)).each(function () {
          
          $('.toggle-button-bulk').on('click', function () {
            console.log(11122)
            const userId = $(this).data('user-id');
            $(`#assignments-${userId}`).toggle();
            $(this).closest('.employee-bulk-assign').toggleClass('hidden-related')
          })
      });
    }
  };
})(jQuery, Drupal);
