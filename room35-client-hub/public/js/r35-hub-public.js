/**
 * Room35 Client Hub - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initTabs();
        initDropzone();
        initUploadForm();
    });

    /**
     * Initialize tab functionality
     */
    function initTabs() {
        var $tabBtns = $('.r35-tab-btn');
        var $tabContents = $('.r35-tab-content');

        $tabBtns.on('click', function() {
            var tabId = $(this).data('tab');

            // Update active button
            $tabBtns.removeClass('active');
            $(this).addClass('active');

            // Update active content
            $tabContents.removeClass('active');
            $('#tab-' + tabId).addClass('active');
        });
    }

    /**
     * Initialize dropzone functionality
     */
    function initDropzone() {
        var $dropzone = $('#r35-client-dropzone');
        var $fileInput = $('#r35-client-file-input');
        var $content = $dropzone.find('.r35-dropzone-content');
        var $selected = $dropzone.find('.r35-file-selected');
        var $selectedName = $dropzone.find('.r35-selected-name');
        var $removeBtn = $dropzone.find('.r35-remove-file');

        // Drag and drop events
        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $dropzone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $fileInput[0].files = files;
                showFileSelected(files[0].name);
            }
        });

        // File input change
        $fileInput.on('change', function() {
            if (this.files.length) {
                showFileSelected(this.files[0].name);
            }
        });

        // Remove file button
        $removeBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $fileInput.val('');
            $content.show();
            $selected.hide();
        });

        function showFileSelected(name) {
            $selectedName.text(name);
            $content.hide();
            $selected.show();
        }
    }

    /**
     * Initialize upload form
     */
    function initUploadForm() {
        var $form = $('#r35-client-upload-form');
        var $progress = $form.find('.r35-upload-progress');
        var $progressFill = $form.find('.r35-progress-fill');
        var $progressText = $form.find('.r35-progress-text');
        var $result = $form.find('.r35-upload-result');
        var $submitBtn = $('#r35-client-upload-btn');
        var $dropzone = $('#r35-client-dropzone');

        $form.on('submit', function(e) {
            e.preventDefault();

            var fileInput = document.getElementById('r35-client-file-input');
            if (!fileInput || !fileInput.files.length) {
                showResult('error', 'Please select a file to upload.');
                return;
            }

            // Prepare form data
            var formData = new FormData();
            formData.append('action', 'r35_client_upload');
            formData.append('nonce', r35HubClient.nonce);
            formData.append('file', fileInput.files[0]);

            // Show progress
            $result.hide();
            $progress.show();
            $progressFill.css('width', '0%');
            $submitBtn.prop('disabled', true).text(r35HubClient.strings.uploading);

            // Upload via AJAX
            $.ajax({
                url: r35HubClient.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percent = Math.round((e.loaded / e.total) * 100);
                            $progressFill.css('width', percent + '%');
                            $progressText.text(r35HubClient.strings.uploading + ' ' + percent + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $progress.hide();
                    $submitBtn.prop('disabled', false).text('Upload File');

                    if (response.success) {
                        showResult('success', r35HubClient.strings.uploadSuccess);
                        // Reset form
                        $form[0].reset();
                        $dropzone.find('.r35-dropzone-content').show();
                        $dropzone.find('.r35-file-selected').hide();

                        // Reload page after 2 seconds to show new file
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showResult('error', response.data.message || r35HubClient.strings.uploadError);
                    }
                },
                error: function(xhr, status, error) {
                    $progress.hide();
                    $submitBtn.prop('disabled', false).text('Upload File');
                    showResult('error', r35HubClient.strings.uploadError);
                }
            });
        });

        function showResult(type, message) {
            $result
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();

            // Scroll to result
            $('html, body').animate({
                scrollTop: $result.offset().top - 100
            }, 300);
        }
    }

})(jQuery);
