# Client File Dashboard - WordPress Plugin

A beautiful and intuitive WordPress plugin that allows administrators to share files with specific clients through a secure, user-friendly dashboard.

## Features

- **Beautiful User Dashboard** - Modern, responsive interface for clients to view and download their files
- **Admin File Management** - Easy-to-use interface for uploading and managing files
- **Category Organization** - Organize files into categories/folders for better structure
- **User-Specific Files** - Assign files to specific users or make them visible to all users
- **Universal Files** - Upload files that are accessible to all logged-in users
- **Secure Access** - Only logged-in users can access the dashboard and their assigned files
- **Multiple File Types** - Support for documents (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX) and images (JPG, PNG, GIF)
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile devices

## Installation

1. Upload the `client-file-dashboard` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Client Files' in the admin menu to start managing files

## Usage

### For Administrators

#### 1. Creating Categories

1. Navigate to **Client Files > Categories** in the WordPress admin
2. Enter a category name and optional description
3. Click "Create Category"
4. Categories will help organize files for better navigation

#### 2. Uploading Files

1. Go to **Client Files > Manage Files**
2. Click "Choose File" and select a file from your computer
3. Select a category (optional)
4. Choose visibility:
   - **Check "Make this file visible to all users"** - All logged-in users can see this file
   - **Leave unchecked** - Assign to specific users by checking their names in the user list
5. Click "Upload File"

#### 3. Managing File Access

1. In the files list, click "Manage Access" next to any file
2. Toggle between universal access or specific user assignments
3. Check/uncheck users to grant or revoke access
4. Click "Save Changes"

#### 4. Deleting Files

- Click the "Delete" button next to any file to remove it
- This will delete both the file record and the physical file

### For Clients

#### Accessing the Dashboard

1. Create a new WordPress page (or edit an existing one)
2. Add the shortcode: `[client_file_dashboard]`
3. Publish the page
4. Share the page URL with your clients

#### Using the Dashboard

1. Clients must log in to their WordPress account
2. They will see all files assigned to them plus universal files
3. Files can be filtered by category using the dropdown
4. Click the "Download" button to download any file
5. The dashboard is fully responsive and works on all devices

## File Types Supported

### Documents
- PDF (.pdf)
- Microsoft Word (.doc, .docx)
- Microsoft Excel (.xls, .xlsx)
- Microsoft PowerPoint (.ppt, .pptx)

### Images
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)

## Security Features

- **Login Required** - Only logged-in WordPress users can access the dashboard
- **File Access Control** - Users can only download files assigned to them or marked as universal
- **Secure Downloads** - Files are served through WordPress with security token verification
- **Protected Directory** - Upload directory is protected with .htaccess rules
- **Nonce Verification** - All AJAX requests are protected with WordPress nonces
- **Capability Checks** - Admin functions require proper WordPress permissions

## Shortcode

```
[client_file_dashboard]
```

Add this shortcode to any page to display the client dashboard.

## Database Tables

The plugin creates three database tables:

- `wp_cfd_categories` - Stores file categories
- `wp_cfd_files` - Stores file metadata
- `wp_cfd_file_assignments` - Stores user-to-file relationships

## Customization

### Styling

You can customize the appearance by adding custom CSS to your theme:

```css
/* Override dashboard colors */
.cfd-dashboard-wrapper {
    /* Your custom styles */
}

/* Customize file cards */
.cfd-file-card {
    /* Your custom styles */
}

/* Customize download button */
.cfd-download-btn {
    /* Your custom styles */
}
```

### Hooks (Coming Soon)

Future versions will include WordPress action and filter hooks for developers.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## File Storage

Files are stored in: `wp-content/uploads/client-file-dashboard/`

This directory is automatically created on plugin activation and protected with .htaccess rules.

## Support

For support, please visit [your support URL] or email [your support email].

## Changelog

### Version 1.0.0
- Initial release
- File upload and management
- Category organization
- User-specific file assignments
- Universal file sharing
- Beautiful responsive dashboard
- Secure file downloads

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by [Your Name]

## Screenshots

1. **Admin File Management** - Upload and manage files with an intuitive interface
2. **Category Management** - Organize files into categories
3. **Client Dashboard** - Beautiful, responsive dashboard for clients
4. **File Assignment** - Easily assign files to specific users

---

Made with ❤️ for WordPress
