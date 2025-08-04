# Deployment Checklist for Hostinger

## Pre-Deployment Preparation
- [x] Configure session handling for shared hosting
- [x] Create .htaccess files for proper routing and security
- [x] Add Hostinger-specific helper class
- [x] Update configuration file with Hostinger options

## Deployment Steps

1. **Create a Database on Hostinger**
   - Log in to your Hostinger control panel
   - Go to "Databases" section
   - Create a new MySQL database
   - Create a database user with necessary permissions
   - Note down the database name, username, and password

2. **Upload Files to Hostinger**
   - Use FTP or Hostinger's File Manager to upload all files
   - Ensure you upload to the correct directory (typically `public_html`)
   - Make sure all files maintain their directory structure

3. **Set Directory Permissions**
   - Set the following permissions:
     - `data/sessions/`: 755 (or 775)
     - `public/uploads/`: 755 (or 775)
     - All PHP files: 644
     - All directories: 755

4. **Update Configuration (if needed)**
   - Edit `config/app.php` if you need to adjust any settings for production
   - Ensure database settings match what you created in step 1
   - Set `'debug' => false` for production

5. **Verify Setup**
   - Browse to your domain and check if the site loads properly
   - Check phpinfo.php (then delete it for security)
   - Test login functionality
   - Test file uploads
   - Ensure sessions work correctly

6. **Security Steps**
   - Delete or restrict access to phpinfo.php
   - Make sure sensitive directories are not accessible via web
   - Ensure proper error handling is in place

7. **Performance Optimization**
   - Enable Hostinger's caching features if available
   - Optimize images if not already done
   - Consider enabling Gzip compression (already in .htaccess)

## Common Issues and Solutions

### Session Problems
- If sessions don't work, try creating a custom session path in your account
- Update `config/app.php` with the new path: `'save_path' => '/path/to/session/dir'`

### Upload Issues
- Check file permissions on upload directories
- Verify PHP file upload settings in phpinfo.php
- Ensure post_max_size and upload_max_filesize are large enough

### 500 Internal Server Error
- Check .htaccess files for compatibility with Hostinger
- Look at error logs in Hostinger control panel
- Temporarily enable debug mode to see detailed errors

### Database Connection Issues
- Double-check database credentials
- Ensure database user has proper permissions
- Verify database connection settings

## After Deployment

- Monitor site performance
- Set up regular backups
- Create proper error logging
- Consider setting up a staging environment for future updates
