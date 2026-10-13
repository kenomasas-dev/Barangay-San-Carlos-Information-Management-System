# Sector Export Feature - Documentation

## Overview
Added the ability to export residents by sector to Excel, including Indigenous People and other demographic groups.

## Changes Made

### 1. **Updated `request_reports.php`**
Added a new "Sector" optgroup to the export filter dropdown with the following options:
- **Indigenous People** - Exports all residents with sector "Indigenous Peoples"
- **Senior Citizens** - Exports all residents with sector "Senior Citizen"
- **PWD** - Exports all residents with sector "PWD (Persons with Disabilities)"
- **Youth** - Exports all residents with sector "Youth"
- **LGBTQ+** - Exports all residents with sector "LGBTQ+"

### 2. **Updated `export_residents.php`**
Added new filter cases in the switch statement:
- `sector_indigenous` → Filters by "Indigenous Peoples"
- `sector_senior` → Filters by "Senior Citizen"
- `sector_pwd` → Filters by "PWD (Persons with Disabilities)"
- `sector_youth` → Filters by "Youth"
- `sector_lgbtq` → Filters by "LGBTQ+"

Each filter generates a descriptive filename suffix for easy identification.

## How to Use

### Step 1: Navigate to Reports
1. Go to the **Reports** page in the admin dashboard
2. Scroll down to the **Resident Records** section

### Step 2: Select Filter
1. Click on the dropdown filter (now 250px wide to accommodate longer text)
2. Expand the **"Sector"** optgroup
3. Select **"Indigenous People"** (or any other sector)

### Step 3: Export to Excel
1. Click the **"Export to Excel"** button
2. The file will download automatically with a descriptive name like:
   - `residents_export_indigenous_people_2025-10-30_12-30-45.xls`

## Export Filename Format

The exported files follow this naming convention:
```
residents_export_{filter}_{date}_{time}.xls
```

Examples:
- `residents_export_indigenous_people_2025-10-30_12-30-45.xls`
- `residents_export_senior_citizens_2025-10-30_12-31-20.xls`
- `residents_export_pwd_2025-10-30_12-32-10.xls`

## Excel File Contents

The exported Excel file includes all resident data:
- Personal Information (Name, Birthdate, Gender, etc.)
- Contact Information (Mobile, Email)
- Demographics (Marital Status, Religion, **Sector**)
- Education Level
- Address Information (Sitio, Purok, House Number)
- Housing Information (Shelter Type, House Material)
- Registration Details (Date Added)

## Database Query

The export uses the following SQL pattern for Indigenous People:
```sql
SELECT * FROM residents 
WHERE sector = 'Indigenous Peoples' 
ORDER BY last_name, first_name
```

## Benefits

1. **Targeted Reports**: Export specific demographic groups for focused analysis
2. **Government Compliance**: Easily generate reports for Indigenous People as required by regulations
3. **Data Analysis**: Analyze demographic trends by sector
4. **Program Planning**: Identify target populations for specific programs
5. **Resource Allocation**: Determine resource needs for different sectors

## Notes

- The sector field must exactly match the database values (e.g., "Indigenous Peoples", "Senior Citizen")
- Exports are sorted alphabetically by last name, then first name
- All fields from the residents table are included in the export
- Files are downloaded in Excel format (.xls) for compatibility
- Filenames include timestamp to prevent overwrites

## Additional Filters Available

The export system also supports:
- **Gender**: Male Only, Female Only
- **Age Groups**: Children (0-17), Adults (18-59), Seniors (60+)
- **Purok**: Purok 1-7
- **All Residents**: No filter applied

## Future Enhancements

Potential additions:
- Occupation-based filters (once occupation field is added to residents table)
- Combined filters (e.g., Indigenous People in Purok 1)
- PDF export option
- Custom column selection
- Date range filters

