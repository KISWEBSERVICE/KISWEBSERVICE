/**
 * Smitty's Gift Cards - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Toggle gift card redemption status
         */
        $('.toggle-redemption').on('click', function() {
            var $button = $(this);
            var giftCardId = $button.data('gift-card-id');
            var isRedeemed = $button.data('is-redeemed');
            var $row = $button.closest('tr');

            if ($button.hasClass('loading')) {
                return;
            }

            var confirmMessage = isRedeemed
                ? 'Are you sure you want to mark this gift card as redeemed?'
                : 'Are you sure you want to mark this gift card as active?';

            if (!confirm(confirmMessage)) {
                return;
            }

            $button.addClass('loading').prop('disabled', true).text('Processing...');

            $.ajax({
                url: smittysGiftCards.ajaxurl,
                type: 'POST',
                data: {
                    action: 'smittys_toggle_redemption',
                    nonce: smittysGiftCards.nonce,
                    gift_card_id: giftCardId,
                    is_redeemed: isRedeemed
                },
                success: function(response) {
                    if (response.success) {
                        // Reload the page to show updated status
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        $button.removeClass('loading').prop('disabled', false);
                        updateButtonText($button, isRedeemed);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.removeClass('loading').prop('disabled', false);
                    updateButtonText($button, isRedeemed);
                }
            });
        });

        /**
         * Resend gift card email
         */
        $('.resend-email').on('click', function() {
            var $button = $(this);
            var giftCardId = $button.data('gift-card-id');

            if ($button.hasClass('loading')) {
                return;
            }

            if (!confirm('Are you sure you want to resend this gift card email?')) {
                return;
            }

            var originalText = $button.text();
            $button.addClass('loading').prop('disabled', true).text('Sending...');

            $.ajax({
                url: smittysGiftCards.ajaxurl,
                type: 'POST',
                data: {
                    action: 'smittys_resend_gift_card',
                    nonce: smittysGiftCards.nonce,
                    gift_card_id: giftCardId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Gift card email sent successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                    $button.removeClass('loading').prop('disabled', false).text(originalText);
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.removeClass('loading').prop('disabled', false).text(originalText);
                }
            });
        });

        /**
         * Update button text based on redemption status
         */
        function updateButtonText($button, isRedeemed) {
            if (isRedeemed) {
                $button.text('Mark Redeemed');
            } else {
                $button.text('Mark Active');
            }
        }
    });

})(jQuery);
