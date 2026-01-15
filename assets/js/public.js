jQuery(document).ready(function($) {
    'use strict';

    // Category filter
    $('#cfd-category-filter').on('change', function() {
        var categoryId = $(this).val();

        if (categoryId === '') {
            // Show all files
            $('.cfd-file-card').fadeIn();
        } else {
            // Show only files in selected category
            $('.cfd-file-card').each(function() {
                if ($(this).data('category-id') == categoryId) {
                    $(this).fadeIn();
                } else {
                    $(this).fadeOut();
                }
            });
        }
    });

    // Download file
    $(document).on('click', '.cfd-download-btn', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var fileId = $btn.data('file-id');
        var originalText = $btn.html();

        $btn.prop('disabled', true).html('<span class="cfd-btn-icon">⏳</span> Downloading...');

        $.ajax({
            url: cfdPublic.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cfd_download_file',
                nonce: cfdPublic.nonce,
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    // Create a temporary link and trigger download
                    window.location.href = response.data.download_url;

                    setTimeout(function() {
                        $btn.prop('disabled', false).html(originalText);
                    }, 2000);
                } else {
                    alert(response.data.message || cfdPublic.strings.downloadError);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert(cfdPublic.strings.downloadError);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Add smooth animations
    $('.cfd-file-card').each(function(index) {
        $(this).css({
            'animation-delay': (index * 0.05) + 's'
        });
    });
});
