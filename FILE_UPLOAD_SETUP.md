# File Upload System - Setup Guide

## Overview
The Records page now includes two tabs:
1. **Bad Records** - Upload and manage Excel files containing bad records
2. **File Uploads** - Upload and manage any type of file (documents, PDFs, images, etc.)

## Database Setup

### Step 1: Create the uploaded_files Table
Run this SQL script in phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(100) DEFAULT NULL,
  `file_size` BIGINT DEFAULT NULL,
  `uploaded_by` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Or simply run the file `create_uploaded_files_table.sql` in phpMyAdmin.

### Step 2: Create the bad_records Table (if not already created)
```sql
CREATE TABLE IF NOT EXISTS `bad_records` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `age` VARCHAR(10) DEFAULT NULL,
  `address` VARCHAR(500) DEFAULT NULL,
  `offense` TEXT NOT NULL,
  `date_recorded` VARCHAR(50) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Active',
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## File Upload Features

### Supported File Types
✅ PDF Documents (.pdf)  
✅ Word Documents (.doc, .docx)  
✅ Excel Spreadsheets (.xls, .xlsx)  
✅ Images (.jpg, .jpeg, .png, .gif)  
✅ Compressed Files (.zip, .rar)  
✅ Any other file type  

### File Size Limit
- Maximum file size: **50 MB**
- Configurable in `upload_file.php`

### Categories
- General
- Documents
- Reports
- Certificates
- Legal
- Financial
- Other

## Usage

### Uploading Files

1. **Navigate to Records Page:**
   - Login to admin dashboard
   - Click "Records" in the sidebar

2. **Switch to File Uploads Tab:**
   - Click on "File Uploads" tab

3. **Upload a File:**
   - Click "Choose File" button
   - Select your file
   - Choose a category from the dropdown
   - (Optional) Add a description
   - Click "Upload File"

4. **View Uploaded Files:**
   - All uploaded files appear in the table below
   - View file details: name, category, size, upload date, etc.
   - Icons automatically match file types

### Downloading Files

- Click the "Download" button next to any file
- File will download to your default downloads folder

### Deleting Files

- Click the "Delete" button next to any file
- Confirm the deletion
- File will be removed from both database and server

## Features

✅ **Dual Tab Interface**
   - Bad Records tab for Excel uploads
   - File Uploads tab for general files

✅ **Smart File Icons**
   - PDF files show PDF icon
   - Word files show document icon
   - Excel files show spreadsheet icon
   - Images show image icon
   - Archives show archive icon

✅ **File Information Display**
   - Original filename
   - File size (formatted in B, KB, MB)
   - Upload date
   - Category badge
   - Description
   - Uploader name

✅ **Secure Storage**
   - Files stored in `uploads/records/` directory
   - Unique filenames prevent overwrites
   - Session-based authentication

✅ **Real-time Feedback**
   - Toast notifications for success/error
   - Automatic page refresh after upload
   - File size validation

## Files Created

- `records.php` - Main records page with tabs
- `upload_file.php` - Handles general file uploads
- `delete_file.php` - Handles file deletion
- `upload_bad_records.php` - Handles Excel bad records upload
- `delete_bad_record.php` - Handles bad record deletion
- `create_uploaded_files_table.sql` - Database table creation
- `create_bad_records_table.sql` - Bad records table creation

## Directory Structure

```
BSCIMS/
├── records.php
├── upload_file.php
├── delete_file.php
├── upload_bad_records.php
├── delete_bad_record.php
└── uploads/
    └── records/
        └── [uploaded files stored here]
```

## Security Features

✅ File size validation (max 50MB)  
✅ Unique filename generation (timestamp + unique ID)  
✅ Session-based access control  
✅ SQL injection protection (prepared statements)  
✅ XSS protection (htmlspecialchars)  
✅ Physical file deletion on database removal  

## Troubleshooting

### Error: "Failed to upload file"
- Check if `uploads/records/` directory exists
- Verify directory has write permissions (777)
- Check file size (must be under 50MB)

### Error: "Database connection failed"
- Verify MySQL is running
- Check database credentials in PHP files
- Ensure `barangay` database exists

### Files not showing in table
- Run the SQL creation script
- Refresh the page
- Check browser console for errors

## Customization

### Change File Size Limit
Edit `upload_file.php`, line 35:
```php
// Change 50 to desired size in MB
if ($fileSize > 50 * 1024 * 1024) {
```

### Add More Categories
Edit `records.php`, File Uploads tab section:
```html
<select class="form-select" name="category" required>
    <option value="General">General</option>
    <option value="YourNewCategory">Your New Category</option>
    <!-- Add more options here -->
</select>
```

### Change Upload Directory
Edit `upload_file.php`, line 46:
```php
$uploadDir = 'uploads/records/';  // Change this path
```

---

For support or questions, contact your system administrator.

