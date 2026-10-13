# Occupation Field Setup Instructions

## Overview
The registration form has been updated to separate occupation-related options from the "Sector" field into a new "Occupation" field.

## Changes Made

### 1. **Form Changes (landing.php)**
- Removed from Sector dropdown: Farmer, Business Owner, Unemployed, Employed
- Created new "Occupation" field with options:
  - Employed
  - Unemployed
  - Farmer
  - Business Owner
  - Student
  - Other

### 2. **Backend Changes (online_reg.php)**
- Added `$occupation = $_POST['occupation'];` to capture the new field
- Updated SQL INSERT statement to include `occupation` column
- Updated bind_param to include the occupation value

### 3. **Database Changes**
- Need to add `occupation` column to `reg_online` table

## Database Setup

### Step 1: Run SQL Script
Open phpMyAdmin and run the SQL script in `add_occupation_column.sql`:

```sql
ALTER TABLE `reg_online` 
ADD COLUMN `occupation` VARCHAR(100) NULL AFTER `education`;
```

### Step 2: Verify
Check that the column was added successfully:
```sql
DESCRIBE reg_online;
```

You should see the new `occupation` column listed after `education`.

## Testing

1. Go to the registration form (landing.php)
2. Fill out all required fields
3. Select a Sector (Youth, Senior Citizen, PWD, Indigenous Peoples, LGBTQ+, Student)
4. Select an Occupation (Employed, Unemployed, Farmer, Business Owner, Student, Other)
5. Submit the form
6. Check the database to ensure the occupation value was saved

## Notes

- The "Occupation" field is required (*)
- Student appears in both Sector and Occupation for flexibility
- The Sector field now focuses on demographic groups
- The Occupation field focuses on employment/work status

