jQuery(document).ready(function ($) {
    // Initialize Color Picker
    var colorPickerOptions = {
        change: function (event, ui) {
            updatePreview();
        },
        clear: function () {
            updatePreview();
        }
    };
    $('.imagero-color-picker').wpColorPicker(colorPickerOptions);

    // Tab Switching Logic
    $('.imagero-tab-link').on('click', function (e) {
        e.preventDefault();
        $('.imagero-tab-link').removeClass('active text-primary border-b-2 border-primary bg-blue-50/50').addClass('text-slate-500 hover:text-slate-700 hover:bg-slate-50');
        $(this).addClass('active text-primary border-b-2 border-primary bg-blue-50/50').removeClass('text-slate-500 hover:text-slate-700 hover:bg-slate-50');
        $('.imagero-tab-pane').addClass('hidden').removeClass('block');
        var target = $(this).data('target');
        $('#' + target).removeClass('hidden').addClass('block');
    });

    // Visual Position Selector Logic
    $('.imagero-position-card').on('click', function () {
        setTimeout(updatePreview, 50);
    });

    // Live Preview Logic
    function updatePreview() {
        var text = $('#imagero_watermark_content').val() || 'Watermark';
        var type = $('#imagero_watermark_type').val();
        var color = $('#imagero_watermark_color').val();
        var opacity = $('#imagero_watermark_opacity').val() || 100;
        var position = $('input[name="imagero_watermark_position"]:checked').val();
        var enabled = $('#imagero_watermark_enabled').is(':checked');

        var $preview = $('#imagero-watermark-preview');

        if (!enabled) {
            $preview.hide();
            return;
        } else {
            $preview.show();
        }

        $preview.css({ 'top': 'auto', 'bottom': 'auto', 'left': 'auto', 'right': 'auto', 'transform': 'none' });

        var margin = '10px';
        switch (position) {
            case 'top-left': $preview.css({ 'top': margin, 'left': margin }); break;
            case 'top-right': $preview.css({ 'top': margin, 'right': margin }); break;
            case 'bottom-left': $preview.css({ 'bottom': margin, 'left': margin }); break;
            case 'bottom-right': $preview.css({ 'bottom': margin, 'right': margin }); break;
            case 'center': $preview.css({ 'top': '50%', 'left': '50%', 'transform': 'translate(-50%, -50%)' }); break;
            default: $preview.css({ 'bottom': margin, 'right': margin });
        }

        if (type === 'image') {
            if (text.match(/\.(jpeg|jpg|gif|png)$/) != null) {
                $preview.html('<img src="' + text + '" style="max-height: 50px; opacity: ' + (opacity / 100) + ';">');
                $preview.css('background', 'transparent');
            } else {
                $preview.text('Image: ' + text);
                $preview.css({ 'color': '#fff', 'background': 'rgba(0,0,0,0.5)', 'opacity': opacity / 100, 'font-size': '14px', 'font-weight': 'bold' });
            }
        } else {
            $preview.text(text);
            $preview.css({ 'color': color, 'background': 'transparent', 'opacity': opacity / 100, 'font-size': '20px', 'font-weight': 'bold', 'text-shadow': '1px 1px 2px rgba(0,0,0,0.5)' });
        }
    }

    $('#imagero_watermark_content, #imagero_watermark_type, #imagero_watermark_opacity, #imagero_watermark_enabled').on('input change', updatePreview);
    updatePreview();

    $('#imagero-save-btn-top').on('click', function (e) {
        e.preventDefault();
        $('#imagero-settings-form').submit();
    });

    $('#imagero-settings-form').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#imagero-save-btn-top');
        var originalText = $btn.text();
        $btn.prop('disabled', true).text('Saving...');
        var formData = $(this).serialize();
        formData += '&action=imagero_save_settings&nonce=' + imagero_ajax.nonce;

        $.ajax({
            url: imagero_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $btn.prop('disabled', false).text(originalText);
                var messageType = response.success ? 'green' : 'red';
                var message = response.data;
                var alertHtml = '<div class="p-4 mb-4 text-sm text-' + messageType + '-800 rounded-lg bg-' + messageType + '-50 border border-' + messageType + '-200" role="alert"><span class="font-medium">' + message + '</span></div>';
                $('#imagero-message-container').html(alertHtml);
                setTimeout(function () { $('#imagero-message-container').empty(); }, 3000);
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
                $('#imagero-message-container').html('<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert"><span class="font-medium">An error occurred.</span></div>');
            }
        });
    });
});
