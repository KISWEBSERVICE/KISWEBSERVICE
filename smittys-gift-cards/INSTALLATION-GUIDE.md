# Installation Guide: cPanel File Manager Method

## Quick Installation Steps

### Step 1: Download the Plugin Package

1. Download the `smittys-gift-cards.zip` file from this repository
2. Save it to your computer (it's about 3-4 MB with all dependencies included)

### Step 2: Upload via cPanel File Manager

1. **Log into your cPanel account**

2. **Navigate to File Manager**
   - Find and click on "File Manager" in the Files section
   - This will open your website's file system

3. **Navigate to the WordPress plugins directory**
   - In the left sidebar, navigate to: `public_html/wp-content/plugins/`
   - (Your path might be different: `public_html/yourdomain.com/wp-content/plugins/` or just `www/wp-content/plugins/`)
   - Look for other plugin folders to confirm you're in the right location

4. **Upload the ZIP file**
   - Click the "Upload" button at the top of File Manager
   - Click "Select File" and choose the `smittys-gift-cards.zip` you downloaded
   - Wait for the upload to complete (you'll see a progress bar)
   - Click "Go Back to..." link to return to File Manager

5. **Extract the ZIP file**
   - Right-click on `smittys-gift-cards.zip` in the file list
   - Select "Extract"
   - In the popup, verify the extraction path is correct (should be `/public_html/wp-content/plugins/`)
   - Click "Extract File(s)"
   - Wait for extraction to complete

6. **Delete the ZIP file (optional but recommended)**
   - Select the `smittys-gift-cards.zip` file
   - Click "Delete" at the top
   - Confirm deletion

7. **Set proper permissions (usually automatic, but check if you have issues)**
   - Select the `smittys-gift-cards` folder
   - Click "Permissions" at the top
   - Ensure it's set to `755` for folders and `644` for files
   - Check "Recurse into subdirectories"
   - Click "Change Permissions"

### Step 3: Add Your Logo

1. **In File Manager, navigate to:**
   ```
   /public_html/wp-content/plugins/smittys-gift-cards/assets/images/
   ```

2. **Upload your logo:**
   - Click "Upload"
   - Upload your Smitty's logo file
   - **Important:** Rename it to exactly `smittys-logo.png`
   - File should be PNG format, at least 400px wide, transparent background recommended

### Step 4: Activate the Plugin

1. **Go to your WordPress Admin Dashboard**
   - Navigate to `yourdomain.com/wp-admin/`

2. **Go to Plugins → Installed Plugins**
   - You should see "Smitty's Gift Cards" in the list

3. **Click "Activate"**
   - The plugin will automatically create the database table needed

4. **Check for any errors**
   - If you see an error about dependencies, see troubleshooting below

### Step 5: Configure Settings (Optional)

1. **Go to Gift Cards → Settings** in your WordPress admin
2. Configure:
   - Email From Name (default: your site name)
   - Email From Address (default: admin email)
   - Email Subject Line
3. Verify your logo is showing (green checkmark)

---

## ✅ What's Included in the ZIP

The ZIP file includes:
- ✅ All plugin files
- ✅ Composer dependencies (PDF library) - **ALREADY INSTALLED**
- ✅ Complete vendor folder - **NO COMMAND LINE NEEDED**

You do **NOT** need to run any commands or install anything via SSH/terminal. Everything is ready to go!

---

## 🎯 Using the Plugin

### Create Your First Gift Card Product

1. **Go to Products → Add New** (or edit existing product)

2. **Set up the product:**
   - Product Name: "Gift Card" or "E-Bike Rental Gift Card"
   - Product Type: "Variable Product" (for variations like 2hr, 4hr, 8hr)

3. **Add Variations:**
   - Click "Attributes" tab
   - Add attribute (e.g., "Duration")
   - Add values: "2 Hours | 4 Hours | 8 Hours"
   - Check "Used for variations"
   - Save attributes

4. **Create Variations:**
   - Go to "Variations" tab
   - Select "Create variations from all attributes"
   - Click through to create them

5. **Enable Gift Card for Each Variation:**
   - Expand each variation
   - Check "This variation is a gift card"
   - Set price
   - Set "Expiration (Months)" (e.g., 12 for 1 year, or leave blank)
   - Save

6. **Publish the product**

### Customer Checkout Experience

When someone buys a gift card:
1. They'll see special fields at checkout:
   - **Recipient Name**
   - **Recipient Email**
   - **Personal Message** (optional)

2. After purchase, recipient receives:
   - Beautiful email with embedded gift card
   - PDF attachment
   - All details included

### Managing Gift Cards

**Access:** WordPress Admin → Gift Cards

**Dashboard shows:**
- Total cards, Active, Redeemed statistics
- Search by name, email, product
- Filter by status and date range

**For phone redemptions:**
1. Customer calls to redeem
2. Search for their gift card in dashboard
3. Click "Mark as Redeemed"
4. Done! System tracks who redeemed it and when

---

## 🔧 Troubleshooting

### "Plugin could not be installed" Error

**Cause:** Usually means you tried uploading through WordPress admin, which has file size limits.

**Solution:** Use cPanel File Manager method above (no size limits).

---

### Missing Dependencies Error

**Symptom:** Error about "Dompdf" or "vendor/autoload.php" not found

**Solution:**
1. The ZIP file INCLUDES all dependencies
2. Make sure you extracted the ENTIRE ZIP file
3. Check that `/wp-content/plugins/smittys-gift-cards/vendor/` folder exists
4. If vendor folder is missing, re-extract the ZIP

---

### Logo Not Showing

**Symptom:** Gift cards have text instead of logo

**Solution:**
1. Upload logo to `/wp-content/plugins/smittys-gift-cards/assets/images/`
2. Must be named **exactly** `smittys-logo.png`
3. Check Settings page to verify it's found

---

### Gift Card Email Not Sending

**Check:**
1. Go to WooCommerce → Settings → Emails
2. Verify emails are working in general
3. Check spam/junk folder
4. Test with your own email first
5. Use "Resend Email" button in Gift Cards dashboard

---

### Can't Find Plugins Folder in cPanel

**Try these paths:**
- `/public_html/wp-content/plugins/`
- `/www/wp-content/plugins/`
- `/httpdocs/wp-content/plugins/`
- `/public_html/yourdomain.com/wp-content/plugins/`

**Tip:** Look for folders named "woocommerce" or "akismet" to confirm you're in the right plugins folder.

---

## 📋 File Structure Check

After installation, verify these files exist:

```
wp-content/plugins/smittys-gift-cards/
├── smittys-gift-cards.php          ✅ Main plugin file
├── vendor/                          ✅ Dependencies folder (3000+ files)
├── includes/                        ✅ Core classes (5 files)
├── templates/                       ✅ HTML templates (2 files)
├── assets/
│   ├── css/                         ✅ Styles
│   ├── js/                          ✅ JavaScript
│   └── images/
│       └── smittys-logo.png         ⚠️ YOU MUST ADD THIS
└── README.md                        ✅ Documentation
```

---

## 🆘 Need More Help?

If you're still having issues:

1. **Check PHP version:** Plugin requires PHP 7.4 or higher
   - Check in cPanel → PHP Selector or MultiPHP Manager

2. **Check WordPress version:** Requires WordPress 5.8+

3. **Check WooCommerce:** Must be installed and active

4. **File permissions:**
   - Folders: 755
   - Files: 644

5. **Contact your hosting support** if you can't access cPanel or need help navigating File Manager

---

## ✨ You're Done!

Once activated, you can:
- ✅ Create gift card products with variations
- ✅ Accept orders with recipient information
- ✅ Send beautiful branded emails with PDFs
- ✅ Manage all gift cards from one dashboard
- ✅ Track redemptions when customers call

**Next:** Create your first gift card product and test it!
