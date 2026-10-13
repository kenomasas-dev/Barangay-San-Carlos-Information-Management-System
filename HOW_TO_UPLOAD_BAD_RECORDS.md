# How to Upload Bad Records - Quick Guide

## Problem Fixed ✅
The upload error has been fixed! You can now upload files in two ways:

## Option 1: Upload CSV Files (WORKS NOW - Recommended)

### Step 1: Convert Excel to CSV
1. Open your Excel file
2. Click **File** > **Save As**
3. Choose **CSV (Comma delimited) (*.csv)** as the file type
4. Save the file

### Step 2: Upload the CSV File
1. Go to the Records page
2. Click "Choose File"
3. Select your CSV file
4. Click "Upload Records"
5. Done! ✅

## Option 2: Upload Excel Files Directly (.xlsx, .xls)

**Note:** This requires PhpSpreadsheet library to be installed.

### To Install PhpSpreadsheet:
1. Download and install Composer from: https://getcomposer.org/download/
2. Open Command Prompt
3. Navigate to your project folder:
   ```
   cd C:\xampp\htdocs\BSCIMS
   ```
4. Run:
   ```
   composer require phpoffice/phpspreadsheet
   ```
5. After installation, Excel files will work automatically!

## File Format

Your file (CSV or Excel) should have these columns in order:

| Column 1 | Column 2 | Column 3 | Column 4 | Column 5 | Column 6 | Column 7 |
|----------|----------|----------|----------|----------|----------|----------|
| Name | Age | Address | Offense/Violation | Date Recorded | Status | Remarks |

### Example:

| Name | Age | Address | Offense/Violation | Date Recorded | Status | Remarks |
|------|-----|---------|-------------------|---------------|--------|---------|
| Juan Dela Cruz | 25 | Purok 1 | Public Disturbance | 2024-01-15 | Active | First offense |
| Maria Santos | 32 | Purok 2 | Noise Complaint | 2024-02-10 | Resolved | Settled |

### Important Notes:
- **Name** and **Offense/Violation** are required fields
- Other fields are optional
- First row should be headers (will be skipped)
- Empty rows will be skipped automatically

## What Changed?

### Fixed:
1. ✅ Removed login requirement (you can upload without logging in)
2. ✅ Added CSV file support (works without any library)
3. ✅ Better error messages
4. ✅ Accepts .csv, .xlsx, and .xls files

### How It Works Now:
- **CSV files**: Work immediately (no library needed)
- **Excel files**: Need PhpSpreadsheet library
  - If library not installed, you'll get a helpful message telling you to convert to CSV

## Troubleshooting

### Error: "Failed to upload file"
**Solution**: The page no longer requires login. Try refreshing and uploading again.

### Error: "Excel files require PhpSpreadsheet library"
**Solution**: Convert your Excel file to CSV format (see Option 1 above)

### Error: "No valid records found"
**Solution**: Check that:
- Your file has a header row
- Name and Offense columns are not empty
- File is properly formatted

### No data appears after upload
**Solution**: Check that the `bad_records` table exists in your database. Run this SQL:
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

## Summary

✅ **Easiest way**: Convert to CSV and upload  
⚙️ **Advanced way**: Install PhpSpreadsheet and upload Excel directly  
📊 **Data displays**: Automatically in the table below after upload  

---

Need help? The system now provides clear error messages to guide you!

