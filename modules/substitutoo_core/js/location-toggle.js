(function ($, Drupal) {
  Drupal.behaviors.locationToggle = {
    attach: function (context, settings) {
      $(once('locationToggle', '#date-form', context)).each(function () {
        $('.toggle-btn', context).on('click', function () {
          const level = $(this).data('level');
          const nextLevelClass = `.nested-level.level-${level}-hidden:first`;
          const $nextLevel = $(this).closest(`.location-level.level-${level}`).find(nextLevelClass);
          $nextLevel.css('display', $nextLevel.css('display') === 'none' ? 'flex' : 'none');  
          $(this).closest('div').toggleClass('hidden-related');
        });

        $('.assignment-toggle', context).on('click', function () {
          const $assignmentCounts = $(this).nextAll('.assignment-counts:first');
          $assignmentCounts.css('display', $assignmentCounts.css('display') === 'none' ? 'flex' : 'none');    
          $(this).closest('div').toggleClass('hidden-related');     
        });
      });

      // Toggle form popup assign
      $(once('toggleAssignments', '.toggle-button', context)).each(function () {
        $(this).on('click', function () {
          var userId = $(this).data('user-id');

          $('.user-assignment[data-user-id="' + userId + '"]').toggleClass('hidden-related');
        });
      });

      $('.toggle-button-bulk').on('click', function () {
        console.log(11122)
        const userId = $(this).data('user-id');
        $(`#assignments-${userId}`).toggle();
        $(this).closest('.employee-bulk-assign').toggleClass('hidden-related')
      })
    }
  };
})(jQuery, Drupal);
