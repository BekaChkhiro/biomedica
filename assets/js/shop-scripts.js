jQuery(document).ready(function($) {
  let isAjaxRunning = false;
  
  // ფილტრაციის მთავარი ფუნქცია
  function filterProducts(page = 1) {
      if (isAjaxRunning) {
          console.log('AJAX request is already running');
          return;
      }
      
      isAjaxRunning = true;
      $('.custom-products-grid').addClass('loading');
      
      // მონიშნული კატეგორიების შეგროვება
      const selectedCategories = [];
      $('.category-filter-checkbox:checked').each(function() {
          selectedCategories.push($(this).val());
      });
      
      const orderby = $('.woocommerce-ordering select').val() || 'menu_order';
      
      $.ajax({
          url: custom_ajax_obj.ajax_url,
          type: 'POST',
          data: {
              action: 'filter_products',
              security: custom_ajax_obj.security,
              categories: selectedCategories,
              orderby: orderby,
              paged: page
          },
          beforeSend: function() {
              // დისეიბლი ყველა ინტერაქტიულ ელემენტზე
              $('.category-filter-checkbox, .woocommerce-ordering select, .page-numbers').prop('disabled', true);
              $('.custom-products-grid').fadeTo('fast', 0.5);
          },
          success: function(response) {
              if (response.success) {
                  // კონტენტის განახლება
                  $('.custom-products-grid').html(response.data.products);
                  $('.custom-pagination-container').html(response.data.pagination);
                  
                  // URL განახლება
                  updateUrlParameters(selectedCategories, orderby, page);
                  
                  // სქროლი
                  $('html, body').animate({
                      scrollTop: $('.custom-products-grid').offset().top - 100
                  }, 300);
              }
          },
          error: function(xhr, status, error) {
              console.error('AJAX Error:', error);
              // შეცდომის შეტყობინება მომხმარებლისთვის
              alert('დაფიქსირდა შეცდომა. გთხოვთ სცადოთ თავიდან.');
          },
          complete: function() {
              isAjaxRunning = false;
              $('.custom-products-grid').removeClass('loading').fadeTo('fast', 1);
              // ინტერაქტიული ელემენტების ჩართვა
              $('.category-filter-checkbox, .woocommerce-ordering select, .page-numbers').prop('disabled', false);
          }
      });
  }
  
  // URL პარამეტრების განახლება
  function updateUrlParameters(categories, orderby, page) {
      const params = new URLSearchParams(window.location.search);
      
      // კატეგორიების პარამეტრები
      params.delete('product_categories[]');
      categories.forEach(cat => {
          params.append('product_categories[]', cat);
      });
      
      // დალაგების პარამეტრი
      if (orderby && orderby !== 'menu_order') {
          params.set('orderby', orderby);
      } else {
          params.delete('orderby');
      }
      
      // გვერდის პარამეტრი
      if (page > 1) {
          params.set('paged', page);
      } else {
          params.delete('paged');
      }
      
      const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
      window.history.pushState({}, '', newUrl);
  }
  
  // Event Listeners
  
  // კატეგორიის ფილტრი
  $(document).on('change', '.category-filter-checkbox', function() {
      filterProducts(1); // პირველ გვერდზე დაბრუნება ფილტრაციისას
  });
  
  // დალაგების ცვლილება
  $(document).on('change', '.woocommerce-ordering select', function() {
      filterProducts(1); // პირველ გვერდზე დაბრუნება დალაგების ცვლილებისას
  });
  
  // პაგინაციის დამუშავება
  $(document).on('click', '.woocommerce-pagination a.page-numbers', function(e) {
      e.preventDefault();
      if (isAjaxRunning) return; // თუ AJAX მიმდინარეობს, არ დავამუშავოთ კლიკი
      
      let page = 1;
      
      // გვერდის ნომრის ამოღება
      if ($(this).hasClass('next')) {
          page = parseInt($('.woocommerce-pagination .current').text()) + 1;
      } else if ($(this).hasClass('prev')) {
          page = parseInt($('.woocommerce-pagination .current').text()) - 1;
      } else {
          page = parseInt($(this).text());
      }
      
      if (isNaN(page)) return;
      
      // აქტიურის კლასის დამატება
      $('.page-numbers').removeClass('current');
      $(this).addClass('current');
      
      filterProducts(page);
  });
  
  // ქვეკატეგორიების ტოგლი
  $('.toggle-subcategories').click(function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const targetId = $(this).data('target');
      const targetElement = $('#' + targetId);
      
      $(this).toggleClass('active');
      targetElement.toggleClass('active').slideToggle(300);
  });
  
  // მშობელი კატეგორიების გახსნა
  function openParentCategories() {
      $('.current-cat').parents('.subcategories').each(function() {
          $(this).addClass('active').show();
          $(this).siblings('.category-header').find('.toggle-subcategories').addClass('active');
      });
  }
  
  // ფილტრის მდგომარეობის აღდგენა
  function restoreFilterState() {
      const params = new URLSearchParams(window.location.search);
      
      // კატეგორიების აღდგენა
      const categories = params.getAll('product_categories[]');
      categories.forEach(catId => {
          $(`.category-filter-checkbox[value="${catId}"]`).prop('checked', true);
      });
      
      // დალაგების აღდგენა
      const orderby = params.get('orderby');
      if (orderby) {
          $('.woocommerce-ordering select').val(orderby);
      }
  }
  
  // ინიციალიზაცია
  openParentCategories();
  restoreFilterState();
});