// custom.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom JavaScript file loaded!');
    // აქ დაამატეთ თქვენი JavaScript კოდი

    const custom_wc_items = document.querySelectorAll('.custom-product-item');
    const productHoverButtons = document.querySelectorAll('.custom-buttons-hover');

    custom_wc_items.forEach(function(item, index) {
        item.addEventListener('mouseover', function() {
            if (productHoverButtons[index]) {
                productHoverButtons[index].classList.add('show');
            }
        });

        item.addEventListener('mouseout', function() {
            if (productHoverButtons[index]) {
                productHoverButtons[index].classList.remove('show');
            }
        });
    });
});


