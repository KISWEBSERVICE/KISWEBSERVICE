jQuery(document).ready(function($) {
    'use strict';

    // Toggle user assignment visibility based on universal checkbox
    $('#cfd-is-universal').on('change', function() {
        if ($(this).is(':checked')) {
            $('#cfd-user-assignment').slideUp();
        } else {
            $('#cfd-user-assignment').slideDown();
        }
    });

    // User search functionality
    $('#cfd-user-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.cfd-user-item').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Modal user search
    $('#cfd-modal-user-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#cfd-modal-user-list .cfd-user-item').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // File upload form
    $('#cfd-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData();
        var fileInput = $('#cfd-file-input')[0];

        if (fileInput.files.length === 0) {
            showMessage('error', 'Please select a file to upload.');
            return;
        }

        formData.append('file', fileInput.files[0]);
        formData.append('category_id', $('#cfd-category-select').val());
        formData.append('is_universal', $('#cfd-is-universal').is(':checked') ? '1' : '0');
        formData.append('action', 'cfd_upload_file');
        formData.append('nonce', cfdAdmin.nonce);

        // Get assigned users
        var assignedUsers = [];
        if (!$('#cfd-is-universal').is(':checked')) {
            $('input[name="assigned_users[]"]:checked').each(function() {
                assignedUsers.push($(this).val());
            });
        }
        formData.append('assigned_users', JSON.stringify(assignedUsers));

        $('#cfd-upload-btn').prop('disabled', true);
        $('.spinner').addClass('is-active');

        $.ajax({
            url: cfdAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage('success', cfdAdmin.strings.uploadSuccess);
                    $('#cfd-upload-form')[0].reset();
                    $('#cfd-user-assignment').hide();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', response.data.message || cfdAdmin.strings.uploadError);
                }
            },
            error: function() {
                showMessage('error', cfdAdmin.strings.uploadError);
            },
            complete: function() {
                $('#cfd-upload-btn').prop('disabled', false);
                $('.spinner').removeClass('is-active');
            }
        });
    });

    // Delete file
    $(document).on('click', '.cfd-delete-file', function() {
        if (!confirm(cfdAdmin.strings.confirmDelete)) {
            return;
        }

        var fileId = $(this).data('file-id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: cfdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cfd_delete_file',
                nonce: cfdAdmin.nonce,
                file_id: fileId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || 'Error deleting file');
                }
            },
            error: function() {
                alert('Error deleting file');
            }
        });
    });

    // Create category form
    $('#cfd-category-form').on('submit', function(e) {
        e.preventDefault();

        var name = $('#cfd-category-name').val();
        var description = $('#cfd-category-description').val();

        $('#cfd-create-category-btn').prop('disabled', true);
        $('.spinner').addClass('is-active');

        $.ajax({
            url: cfdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cfd_create_category',
                nonce: cfdAdmin.nonce,
                name: name,
                description: description
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', 'Category created successfully!', '#cfd-category-message');
                    $('#cfd-category-form')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('error', response.data.message || 'Error creating category', '#cfd-category-message');
                }
            },
            error: function() {
                showMessage('error', 'Error creating category', '#cfd-category-message');
            },
            complete: function() {
                $('#cfd-create-category-btn').prop('disabled', false);
                $('.spinner').removeClass('is-active');
            }
        });
    });

    // Delete category
    $(document).on('click', '.cfd-delete-category', function() {
        if (!confirm(cfdAdmin.strings.confirmDeleteCategory)) {
            return;
        }

        var categoryId = $(this).data('category-id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: cfdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cfd_delete_category',
                nonce: cfdAdmin.nonce,
                category_id: categoryId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || 'Error deleting category');
                }
            },
            error: function() {
                alert('Error deleting category');
            }
        });
    });

    // Manage file assignments - Modal
    var currentFileId = null;

    $(document).on('click', '.cfd-manage-assignments', function() {
        currentFileId = $(this).data('file-id');
        var $row = $(this).closest('tr');
        var isUniversal = $row.find('.cfd-badge-universal').length > 0;

        // Reset modal
        $('#cfd-modal-universal').prop('checked', isUniversal);
        $('#cfd-modal-user-list input[type="checkbox"]').prop('checked', false);

        if (isUniversal) {
            $('#cfd-modal-user-assignment').slideUp();
        } else {
            $('#cfd-modal-user-assignment').slideDown();
            // Load current assignments
            loadFileAssignments(currentFileId);
        }

        $('#cfd-assignments-modal').fadeIn();
    });

    // Modal universal checkbox toggle
    $('#cfd-modal-universal').on('change', function() {
        if ($(this).is(':checked')) {
            $('#cfd-modal-user-assignment').slideUp();
        } else {
            $('#cfd-modal-user-assignment').slideDown();
        }
    });

    // Close modal
    $('.cfd-modal-close, #cfd-cancel-assignments').on('click', function() {
        $('#cfd-assignments-modal').fadeOut();
    });

    // Save assignments
    $('#cfd-save-assignments').on('click', function() {
        var userIds = [];
        $('#cfd-modal-user-list input[type="checkbox"]:checked').each(function() {
            userIds.push($(this).val());
        });

        $.ajax({
            url: cfdAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cfd_assign_file',
                nonce: cfdAdmin.nonce,
                file_id: currentFileId,
                user_ids: JSON.stringify(userIds)
            },
            success: function(response) {
                if (response.success) {
                    $('#cfd-assignments-modal').fadeOut();
                    location.reload();
                } else {
                    alert(response.data.message || 'Error updating assignments');
                }
            },
            error: function() {
                alert('Error updating assignments');
            }
        });
    });

    // Load file assignments
    function loadFileAssignments(fileId) {
        // This would require an additional AJAX endpoint to get current assignments
        // For simplicity, we'll leave checkboxes unchecked and let admin reselect
    }

    // Helper function to show messages
    function showMessage(type, message, selector) {
        var $messageEl = $(selector || '#cfd-upload-message');
        $messageEl.removeClass('cfd-message-success cfd-message-error')
                  .addClass('cfd-message-' + type)
                  .html(message)
                  .slideDown();

        setTimeout(function() {
            $messageEl.slideUp();
        }, 5000);
    }

    // Close modal on outside click
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('cfd-modal')) {
            $('.cfd-modal').fadeOut();
        }
    });
});
