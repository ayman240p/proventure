# ProVenture Admin Panel - Setup Guide

## 📋 Setup Instructions

### Step 1: Run the Database Migrations

Open phpMyAdmin (http://localhost/phpmyadmin) and run these SQL files in order:

1. **Create Admin Role and Account**
   - Visit: http://localhost/cshub/create_admin.php
   - This will automatically:
     - Add 'admin' role to the users table
     - Create admin account: `admin@cshub.com` / `admin123`
   - **IMPORTANT:** Delete the `create_admin.php` file after running it

2. **Add Activity Logs Table**
   - Go to phpMyAdmin → cshub database → SQL tab
   - Import: `C:\xampp\htdocs\cshub\database\add_activity_logs.sql`
   - Or copy and paste the SQL from that file

### Step 2: Login as Admin

1. Visit: http://localhost/cshub/login.php
2. Email: `admin@cshub.com`
3. Password: `admin123`
4. You'll be redirected to the admin dashboard

---

## 🎉 New Features Implemented

### 1. **Password Change Functionality** ✓
   - **Location:** Admin → Settings
   - **Features:**
     - Change your admin password securely
     - Update profile information (name, email, phone)
     - View your recent activity
     - See account statistics

### 2. **Admin Activity Logs** ✓
   - **Location:** Admin Dashboard → Activity Logs
   - **Features:**
     - Track all admin actions (deletions, updates, exports)
     - Filter by admin, action type, or date
     - View IP addresses and timestamps
     - Statistics: total actions, today's actions, deletions count
   - **What gets logged:**
     - User deletions
     - Service deletions/status changes
     - Category additions/deletions
     - Review deletions
     - Bulk operations
     - Password changes
     - Data exports

### 3. **Bulk Actions** ✓
   - **Location:** Available on Users, Services, and Reviews pages
   - **Features:**
     - **Users:** Bulk delete multiple users
     - **Services:** Bulk delete, activate, or deactivate services
     - **Reviews:** Bulk delete reviews
     - Select all checkbox for quick selection
     - Shows count of selected items
     - Confirmation dialogs for safety

### 4. **Data Export (CSV/Excel)** ✓
   - **Location:** Export buttons on all management pages
   - **Features:**
     - Export Users (with roles, emails, phones)
     - Export Services (with provider info, categories, prices)
     - Export Reviews (with ratings, comments, service info)
     - Export Categories (with service counts)
     - UTF-8 encoding for special characters
     - Automatic filename with timestamp
   - **How to use:** Click "Export CSV" button on any management page

### 5. **Dashboard Charts & Statistics** ✓
   - **Location:** Admin Dashboard (main page)
   - **Features:**
     - Real-time statistics cards
     - Total clients, freelancers, services counts
     - Active services indicator
     - Recent users and services tables
     - Quick action buttons to all admin pages

### 6. **Configurable Automatic Service Approval** ✓
   - **Location:** Admin → Settings → Platform Moderation & Approval Settings
   - **Features:**
     - **Toggle Switch:** Turn Auto-Approval ON or OFF at any time
     - **When Enabled (ON):** Newly submitted and edited micro-services from freelancers are automatically approved and published live immediately. Guarantees zero platform downtime even if an administrator is absent or inactive.
     - **When Disabled (OFF):** Services are held in the moderation queue with `pending` status until manually reviewed.
     - **Audit Logging:** Every time the setting is toggled, it is logged in the Activity Logs.

### 7. **Community Listing Reporting & Moderation Center** ✓
   - **Location:**
     - **Public:** Direct "Report Listing" button on all live service pages (`service.php`), search results (`search.php`), and business directory (`business.php`).
     - **Admin:** Admin Dashboard → Community Reports (`/cshub/admin/reports.php`).
   - **Features:**
     - **Community Safeguard:** Users & guests can report listings for Spam/Scams, Malicious Content/Links, Misleading Information, or Inappropriate text.
     - **Admin Reports Center:** Filter reports by pending, action taken, or dismissed.
     - **1-Click Actions:** Instant "Suspend / Reject Service" (hides service from public view immediately) or "Dismiss" (marks as false alarm).
     - **Dashboard Badge & Banner:** Red alert banner on Admin Dashboard when unresolved reports are pending review.
     - **Services Table Badge:** Services with pending reports display a red `🚩 Report(s)` badge in the service moderation table.

---

## 📱 Admin Panel Navigation

### Main Dashboard
- **URL:** `/cshub/admin/dashboard.php`
- Overview of all statistics
- Quick access to all management pages
- Recent activity preview

### User Management
- **URL:** `/cshub/admin/users.php`
- View all users (clients, freelancers, admins)
- Search by name/email
- Filter by role
- Bulk delete users
- Export to CSV
- Individual user deletion

### Service Management
- **URL:** `/cshub/admin/services.php`
- View all services
- Search by title/description/provider
- Filter by category and availability
- Bulk delete/activate/deactivate
- Toggle individual service availability
- Export to CSV

### Category Management
- **URL:** `/cshub/admin/categories.php`
- View all categories with service counts
- Add new categories
- Delete categories
- Export to CSV

### Review Management
- **URL:** `/cshub/admin/reviews.php`
- View all reviews with ratings
- See which service and provider
- Bulk delete inappropriate reviews
- Export to CSV

### Activity Logs
- **URL:** `/cshub/admin/activity-logs.php`
- Track all admin actions
- Filter by admin, action, date
- View detailed activity history

### Admin Settings
- **URL:** `/cshub/admin/settings.php`
- Change password
- Update profile information
- View your personal activity

---

## 🔒 Security Features

1. **Role-Based Access Control**
   - Only admin role can access admin pages
   - Session validation on every page
   - Protection against unauthorized access

2. **Activity Logging**
   - Every admin action is logged
   - IP address tracking
   - Timestamps for audit trails

3. **Password Security**
   - Password hashing with `password_hash()`
   - Current password verification required
   - Minimum 6 character requirement

4. **SQL Injection Protection**
   - All queries use PDO prepared statements
   - No direct SQL concatenation

5. **XSS Protection**
   - All outputs use `htmlspecialchars()`
   - Proper escaping of user input

6. **Self-Protection**
   - Admin cannot delete their own account
   - Prevents accidental lockout

---

## 📊 How to Use Bulk Actions

### For Users:
1. Go to Admin → Users
2. Check the boxes next to users you want to delete
3. Or click "Select All" to select all users
4. Click "Delete Selected"
5. Confirm the action

### For Services:
1. Go to Admin → Services
2. Select services using checkboxes
3. Choose action:
   - **Delete Selected** - Remove services
   - **Activate Selected** - Make services available
   - **Deactivate Selected** - Make services busy
4. Confirm the action

---

## 📥 How to Export Data

1. Navigate to any management page (Users, Services, Reviews, Categories)
2. Click the **"Export CSV"** button (green button)
3. File will automatically download with format:
   `cshub_[type]_YYYY-MM-DD_HHMMSS.csv`
4. Open with Excel, Google Sheets, or any spreadsheet software

---

## 🎯 Quick Tips

1. **Change Default Password**
   - Go to Admin → Settings
   - Use a strong password (8+ characters, mixed case, numbers)

2. **Monitor Activity Regularly**
   - Check Activity Logs weekly
   - Look for suspicious actions
   - Track who deleted what

3. **Use Bulk Actions Carefully**
   - Always review selections before confirming
   - Deletions cannot be undone
   - Export data before bulk deletions

4. **Regular Data Exports**
   - Export data weekly for backup
   - Keep CSV files in a safe location
   - Useful for reporting and analysis

---

## 🚀 Admin Dashboard Features

### Statistics Cards
- **Total Clients** - Blue card
- **Total Freelancers** - Green card
- **Total Services** - Blue card (with active count)

### Recent Tables
- **Recent Users** - Last 5 registered users
- **Recent Services** - Last 5 created services

### Quick Action Buttons
- Manage Users
- Manage Services
- Manage Categories
- Manage Reviews
- Activity Logs
- Settings
- View Site (opens in new tab)
- Logout

---

## 🔧 Troubleshooting

### Activity Logs Not Working?
- Make sure you ran `add_activity_logs.sql`
- Check if the `activity_logs` table exists in phpMyAdmin

### Bulk Actions Not Working?
- Check browser console for JavaScript errors
- Make sure checkboxes are visible and clickable
- Ensure you selected at least one item

### Export Not Downloading?
- Check if your browser is blocking downloads
- Try right-click → Save Link As
- Check PHP error logs in XAMPP

### Can't Login as Admin?
- Make sure you ran `create_admin.php` first
- Check if email is `admin@cshub.com` (no spaces)
- Password is `admin123` (all lowercase)

---

## 📝 Files Created

### New PHP Files:
- `/admin/activity-logs.php` - Activity logs viewer
- `/admin/settings.php` - Admin settings & password change
- `/admin/bulk-actions.php` - Bulk operations handler
- `/admin/export.php` - CSV export handler
- `/includes/logger.php` - Activity logging functions

### New SQL Files:
- `/database/add_activity_logs.sql` - Activity logs table
- `/database/add_admin_role.sql` - Admin role migration

### New Features Added to Existing Files:
- All management pages now have:
  - Export buttons
  - Bulk action checkboxes
  - Activity logging
  - Success/error messages

---

## 🎨 UI Enhancements

- Professional gradient background
- Modern card-based layouts
- Responsive design for mobile
- Color-coded badges for roles and statuses
- SVG icons for actions
- Smooth hover effects
- Bootstrap 5 styling throughout

---

## 📈 Next Steps

After setup, you can:
1. ✅ Test the admin login
2. ✅ Change the default admin password
3. ✅ Create some test users (register as client/freelancer)
4. ✅ Test bulk actions
5. ✅ Export data to CSV
6. ✅ Check activity logs
7. ✅ Add some categories
8. ✅ Have freelancers add services
9. ✅ Test all management features

---

## 🎓 For Your IT Project Report

When documenting this admin panel for your project report, highlight:

1. **Security Implementation**
   - Role-based access control
   - Activity logging for audit trails
   - Secure password management

2. **Admin Features**
   - Complete CRUD operations for all entities
   - Bulk operations for efficiency
   - Data export for reporting and backup

3. **User Experience**
   - Modern, responsive interface
   - Real-time statistics dashboard
   - Search and filter capabilities

4. **Best Practices**
   - PDO prepared statements
   - Session management
   - Input validation and sanitization
   - XSS and SQL injection protection

---

## 🆘 Support

If you encounter any issues:
1. Check XAMPP (Apache & MySQL are running)
2. Check browser console for errors
3. Check PHP error logs in XAMPP
4. Verify database tables exist in phpMyAdmin

---

**Happy administrating! 🎉**

Your ProVenture admin panel is now a powerful, secure, and feature-rich system!
