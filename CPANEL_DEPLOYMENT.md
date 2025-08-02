# cPanel Deployment Configuration

This directory contains cPanel deployment configuration files for automatically deploying your OSPOS application.

## Files

- `.cpanel.yml` - Main deployment configuration for root directory deployment
- `.cpanel-subdirectory.yml` - Alternative configuration for subdirectory deployment

## Setup Instructions

### 1. Enable Git Version Control in cPanel

1. Log into your cPanel
2. Go to **Git Version Control**
3. Click **Create** to add a new repository
4. Enter your repository URL: `https://github.com/sahand009/opensourcepos.git`
5. Choose the repository path (usually `/public_html/` for main domain)
6. Select the branch you want to deploy (usually `master`)
7. Click **Create**

### 2. Configure Deployment

The `.cpanel.yml` file will automatically:

✅ **Deploy Files** - Copy all necessary OSPOS files to your web directory  
✅ **Set Permissions** - Configure proper file permissions for directories  
✅ **Preserve Settings** - Backup and restore your `.env` configuration  
✅ **Clean Up** - Remove development files not needed in production  
✅ **Create Directories** - Set up required upload directories  

### 3. Deployment Options

#### Option A: Root Directory Deployment (Default)
Uses `.cpanel.yml` - deploys to `/public_html/`

Your site will be accessible at: `https://yourdomain.com`

#### Option B: Subdirectory Deployment
1. Rename `.cpanel-subdirectory.yml` to `.cpanel.yml`
2. Edit the `DEPLOYPATH` variable to your preferred subdirectory
3. Default deploys to `/public_html/ospos/`

Your site will be accessible at: `https://yourdomain.com/ospos`

### 4. Manual Deployment

To manually trigger deployment in cPanel:

1. Go to **Git Version Control**
2. Find your repository
3. Click **Manage**
4. Click **Pull or Deploy** tab
5. Click **Deploy HEAD Commit**

### 5. Automatic Deployment

To enable automatic deployment when you push to GitHub:

1. In cPanel Git Version Control, click **Manage**
2. Enable **Auto Deploy** option
3. Now every push to your selected branch will automatically deploy

### 6. Post-Deployment Steps

After deployment, you need to:

1. **Configure Database**
   - Copy `.env.example` to `.env`
   - Edit `.env` with your database credentials

2. **Set Up Database**
   - Import the OSPOS database schema
   - Run any necessary migrations

3. **Test Application**
   - Visit your deployed site
   - Verify login functionality
   - Check that Turnstile is working correctly

### 7. Security Notes

The deployment script automatically removes:
- Development files (`*.md`, test files, build scripts)
- Git repository data
- Composer files
- Debug scripts
- Management scripts

This ensures your production environment is clean and secure.

### 8. Troubleshooting

**Permission Issues:**
- The script sets proper permissions automatically
- If you still have issues, manually set permissions:
  ```bash
  chmod -R 755 writable/
  chmod -R 755 public/uploads/
  ```

**Database Connection:**
- Verify your `.env` file has correct database settings
- Check that your database exists and is accessible

**File Not Found:**
- Ensure the deployment path in `.cpanel.yml` matches your hosting setup
- Common paths: `/public_html/`, `/public_html/subdomain/`, `/public_html/addon-domain/`

### 9. Customization

To customize the deployment:

1. Edit `.cpanel.yml`
2. Modify the `DEPLOYPATH` variable for different directories
3. Add or remove file cleanup commands as needed
4. Adjust permission settings if required

### 10. Branch Strategy

For production deployment:
- Use `master` branch for stable releases
- Test features in `feature/*` branches first
- The automatic sync with upstream ensures you get security updates
