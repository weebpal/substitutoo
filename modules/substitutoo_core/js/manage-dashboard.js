(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.manage_booking = {
    attach: function (context, settings) {
      $(once('manage_booking', '#datepicker', context)).each(function () {
        $(this).Monthpicker()
        var month = $('.monthpicker').find('td')
        month.on('click', function (e) {
          var text = $('.monthpicker_input').text()
          if (text.length > 0) {
            var parts = text.split(' '); 
            var month_name = parts[0]; 
            var year = parts[1]; 
            var month_number = new Date(Date.parse(month_name + " 1, 2000")).getMonth() + 1;
            var newUrl = window.location.origin + window.location.pathname + '?month=' + month_number + '&year=' + year;
            window.location.href = newUrl;
          }          
        })
      });
      $(once('date_picker', 'body', context)).each(function () {
        //**month picker */
        $('.calendar-header').on('click', function (e) {
          $('.monthpicker_selector').show()
        })

        //**Move table month picker */
        var table_month_picker = $('.monthpicker')
        $('.calendar-navigation').append(table_month_picker)
      });
      $(document).on('click', function (e) {
        if ($(e.target).closest('.monthpicker').length === 0 && !$('h2').is(e.target)) {
          $('.monthpicker_selector').hide()
        }
      });

      // $(once('horizontalScroll', '.view-table', context)).each(function () {
      //   const element = document.querySelector(".view-table");

      //   element.addEventListener('wheel', (event) => {
      //     event.preventDefault();

      //     element.scrollBy({
      //       left: event.deltaY < 0 ? -30 : 30,
            
      //     });
      //   });
      // });
      $(once('horizontalScroll', '.view-table', context)).each(function () {
        if (window.innerWidth < 1900) {
          const element = document.querySelector(".view-table");
      
          element.addEventListener('wheel', (event) => {
            event.preventDefault();
      
            element.scrollBy({
              left: event.deltaY < 0 ? -30 : 30,
              behavior: 'smooth',
            });
          });
        }
      });
      

      $(once('fixedTableHeader', '.calendar-view-table', context)).each(function () {
        const $table = $(this);
        const $thead = $table.find('thead'); 
        const tableOffsetTop = $table.offset().top; 
        const $window = $(window);
  
        function checkScroll() {
          const scrollTop = $window.scrollTop(); 
  
          if (scrollTop > tableOffsetTop) {
            $thead.addClass('fixed');
          } else {
            $thead.removeClass('fixed');
          }
        }
  
        $window.on('scroll', checkScroll);
  
        checkScroll();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);