# Smitty's Gift Cards for WooCommerce

A comprehensive digital gift card plugin for WordPress and WooCommerce that allows you to sell gift cards as products with variations, send beautifully designed emails, and manage redemptions through an admin dashboard.

## Features

- ✅ **WooCommerce Integration** - Use regular WooCommerce products with variations as gift cards
- ✅ **Dual Delivery** - Sends both PDF attachment and HTML email version
- ✅ **Custom Recipient Info** - Collect recipient name, email, and personal message at checkout
- ✅ **Flexible Expiration** - Set expiration periods per product or variation
- ✅ **Beautiful Design** - Professional gift card design with Smitty's E-Bikes branding
- ✅ **Admin Dashboard** - Comprehensive management interface with search and filters
- ✅ **Redemption Tracking** - Mark gift cards as redeemed with date and user tracking
- ✅ **Resend Capability** - Resend gift card emails from the admin panel
- ✅ **Phone Redemption** - Designed for phone-based redemption process

## Installation

### 1. Upload Plugin Files

Upload the `smittys-gift-cards` folder to your WordPress plugins directory:
```
/wp-content/plugins/smittys-gift-cards/
```

### 2. Install Dependencies

Navigate to the plugin directory and install dependencies using Composer:

```bash
cd /path/to/wordpress/wp-content/plugins/smittys-gift-cards
composer install
```

This will install the Dompdf library required for PDF generation.

### 3. Add Your Logo

Place your Smitty's E-Bikes logo as `smittys-logo.png` in:
```
/wp-content/plugins/smittys-gift-cards/assets/images/smittys-logo.png
```

**Logo Requirements:**
- Format: PNG with transparent background (recommended)
- Minimum width: 400px
- The plugin will use a text-based fallback if no logo is found

### 4. Activate the Plugin

1. Go to WordPress Admin → Plugins
2. Find "Smitty's Gift Cards" in the list
3. Click "Activate"

The plugin will automatically create the necessary database table for gift card tracking.

## Usage

### Setting Up Gift Card Products

1. **Create or Edit a Product**
   - Go to Products → Add New (or edit an existing product)
   - Configure your product as usual

2. **Enable Gift Card**
   - Scroll to the "Product data" section
   - Check the box "Gift Card Product"
   - Set the expiration period in months (or leave blank for no expiration)

3. **For Variable Products**
   - Create your variations (e.g., "2 Bike Rental - 2 Hours", "2 Bike Rental - 4 Hours")
   - In each variation, check "This variation is a gift card"
   - Set the expiration period for each variation

### Customer Experience

When a customer purchases a gift card:

1. During checkout, they'll see additional fields:
   - Recipient Name
   - Recipient Email
   - Personal Message (optional)

2. After purchase, the recipient receives an email containing:
   - Beautiful HTML gift card (printable from browser)
   - PDF attachment of the gift card
   - Purchase date and expiration date (if set)
   - Personal message from the sender
   - Product variation details

### Managing Gift Cards

Access the gift card dashboard from **WordPress Admin → Gift Cards**

#### Dashboard Features

**Statistics Overview:**
- Total gift cards issued
- Active gift cards
- Redeemed gift cards

**Search & Filter:**
- Search by recipient name, email, or product
- Filter by status (Active/Redeemed)
- Filter by date range

**Gift Card Actions:**
- **Mark as Redeemed** - Track when customers call to redeem
- **Mark as Active** - Undo redemption if needed
- **Resend Email** - Resend the gift card email to the recipient

**Gift Card Information:**
- Recipient details
- Product/variation name
- Purchase and expiration dates
- Redemption status and history
- Link to original order

### Plugin Settings

Go to **Gift Cards → Settings** to configure:

- **Email From Name** - Name that appears in gift card emails
- **Email From Address** - Email address for sending gift cards
- **Email Subject** - Subject line for gift card emails
- **Logo Status** - Check if your logo file is properly installed

## Technical Details

### Database Structure

The plugin creates a custom table `wp_smittys_gift_cards` with the following structure:

- `id` - Unique gift card ID
- `order_id` - WooCommerce order ID
- `product_id` - Product ID
- `variation_id` - Variation ID (if applicable)
- `recipient_name` - Recipient's name
- `recipient_email` - Recipient's email
- `custom_message` - Personal message from sender
- `purchase_date` - Date of purchase
- `expiration_date` - Expiration date (if set)
- `product_variation_name` - Full product/variation name
- `is_redeemed` - Redemption status (0 or 1)
- `redeemed_date` - Date when redeemed
- `redeemed_by` - User who marked as redeemed

### Hooks and Filters

**Actions:**
```php
// Send gift card email
do_action('smittys_send_gift_card_email', $gift_card_id, $order_id);
```

**Filters:**
```php
// Customize email HTML (future enhancement)
apply_filters('smittys_gc_email_html', $html, $data);
```

### File Structure

```
smittys-gift-cards/
├── smittys-gift-cards.php          # Main plugin file
├── composer.json                    # Composer dependencies
├── README.md                        # This file
├── includes/
│   ├── class-gift-card-product.php  # Product meta handling
│   ├── class-gift-card-checkout.php # Checkout fields & order processing
│   ├── class-gift-card-email.php    # Email generation & sending
│   ├── class-gift-card-pdf.php      # PDF generation
│   └── class-gift-card-admin.php    # Admin dashboard & management
├── templates/
│   ├── email-template.php           # Email HTML template
│   └── admin-dashboard.php          # Admin interface template
├── assets/
│   ├── css/
│   │   └── admin.css                # Admin styling
│   ├── js/
│   │   └── admin.js                 # Admin JavaScript
│   └── images/
│       └── smittys-logo.png         # Your logo file (add this)
└── vendor/                          # Composer dependencies (auto-generated)
```

## Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **WooCommerce:** 5.0 or higher
- **Composer:** For installing PDF library

## Branding

The plugin uses Smitty's E-Bikes branding with:
- **Primary Color:** Red (#E31E24)
- **Secondary Color:** Dark Blue (#1e3a5f)
- **Accent Color:** Orange (#F47920)
- **Text:** Black or white depending on background

## Support

For issues or questions:
1. Check the plugin settings to ensure everything is configured correctly
2. Verify your logo file is in the correct location
3. Ensure Composer dependencies are installed
4. Check WordPress and WooCommerce error logs

## License

GPL v2 or later

## Credits

Developed for Smitty's E-Bikes
© 2026 Smitty's E-Bikes. All rights reserved.
