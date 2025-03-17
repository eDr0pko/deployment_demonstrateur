// Funnction to toggle the visibility of the explanation of the labels
$(document).ready(function () {
    $('.label').click(function () {
        const explanation = $(this).next('.explanation');
        const icon = $(this).find('.toggle-icon');

        explanation.toggleClass('visible');

        if (explanation.hasClass('visible')) {
            icon.text('−');
        } else {
            icon.text('+');
        }
    });
});


