# Materials Management System - Testing Guide

## ✅ System Implementation Status

All components of the Materials Management System have been successfully implemented according to the laboratory activity requirements.

## 📋 Implemented Components

### Step 1: Database Migration ✅
- **File**: `app/Database/Migrations/2025-10-23-161710_CreateMaterialsTable.php`
- **Status**: ✅ Migration has been run successfully
- **Table**: `materials` with fields:
  - `id` (Primary Key, Auto-Increment)
  - `course_id` (Foreign Key to courses table)
  - `file_name` (VARCHAR 255)
  - `file_path` (VARCHAR 255)
  - `created_at` (DATETIME)

### Step 2: MaterialModel ✅
- **File**: `app/Models/MaterialModel.php`
- **Methods Implemented**:
  - `insertMaterial($data)` - Insert new material record
  - `getMaterialsByCourse($course_id)` - Get all materials for a course
  - `getMaterialById($id)` - Get material by ID
  - `deleteMaterial($id)` - Delete material record

### Step 3: Materials Controller ✅
- **File**: `app/Controllers/Materials.php`
- **Methods Implemented**:
  - `upload($course_id)` - Display upload form and handle uploads
  - `delete($material_id)` - Delete material and file
  - `download($material_id)` - Handle secure file downloads
  - `handleUpload($course_id)` - Process file upload (private method)

### Step 4: File Upload Functionality ✅
- **Features**:
  - File type validation (PDF, PPT, PPTX, DOC, DOCX)
  - File size validation (Max 10MB)
  - Secure file storage in `writable/uploads/materials/`
  - Random filename generation for security
  - Database record creation
  - Flash messages for success/error feedback

### Step 5: Upload View ✅
- **File**: `app/Views/materials/upload.php`
- **Features**:
  - Bootstrap-styled upload form
  - File input with accept attribute
  - List of existing materials
  - Download and delete buttons
  - Confirmation dialog for deletion

### Step 6: Student Dashboard Integration ✅
- **File**: `app/Views/auth/dashboard.php` (Updated)
- **Features**:
  - Display materials for enrolled courses
  - Download links for each material
  - Material upload date display
  - File type icons
  - Responsive design

### Step 7: Download Method ✅
- **Security Features**:
  - Login verification
  - Enrollment verification (students can only download from enrolled courses)
  - File existence check
  - Secure file download using CodeIgniter's response download method

### Step 8: Routes Configuration ✅
- **File**: `app/Config/Routes.php`
- **Routes Added**:
  ```php
  $routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
  $routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
  $routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
  $routes->get('/materials/download/(:num)', 'Materials::download/$1');
  ```

### Step 9: Additional Features ✅
- **Admin Dashboard**: Course management with material upload links
- **Teacher Dashboard**: Course list with material management options
- **Security**: 
  - .htaccess file preventing direct file access
  - index.html files in upload directories
  - Role-based access control
  - CSRF protection

## 🧪 Testing Instructions

### Test 1: Admin/Teacher Material Upload
1. Start the server: `php spark serve`
2. Navigate to: `http://localhost:8080/login`
3. Login as admin or teacher
4. Go to Dashboard
5. Click "Upload Materials" button for any course
6. Select a PDF, PPT, or DOC file (max 10MB)
7. Click "Upload Material"
8. ✅ Expected: Success message and file appears in the list

### Test 2: Student Material Access
1. Login as a student
2. Go to Dashboard
3. Find an enrolled course with materials
4. Click the download button (📥 icon)
5. ✅ Expected: File downloads successfully

### Test 3: Enrollment Restriction
1. Login as a student
2. Note the URL of a material download link: `/materials/download/{id}`
3. Find a course you're NOT enrolled in with materials
4. Try to access its download link directly
5. ✅ Expected: "You must be enrolled in this course" error message

### Test 4: Material Deletion (Admin/Teacher)
1. Login as admin or teacher
2. Go to upload page for a course
3. Click "Delete" button on a material
4. Confirm deletion
5. ✅ Expected: Material removed from database and file deleted from server

### Test 5: File Type Validation
1. Login as admin/teacher
2. Go to upload page
3. Try uploading an image file (.jpg, .png)
4. ✅ Expected: Error message about invalid file type

### Test 6: File Size Validation
1. Try uploading a file larger than 10MB
2. ✅ Expected: Error message about file size

### Test 7: Role-Based Access Control
1. Login as a student
2. Try to access: `http://localhost:8080/admin/course/1/upload`
3. ✅ Expected: "Access denied" message

## 📁 Directory Structure

```
ITE311-DAYAO/
├── app/
│   ├── Controllers/
│   │   ├── Auth.php (Updated with materials loading)
│   │   ├── Course.php (Enrollment functionality)
│   │   └── Materials.php (New - Material management)
│   ├── Models/
│   │   ├── MaterialModel.php (New)
│   │   ├── CourseModel.php
│   │   ├── EnrollmentModel.php
│   │   └── UserModel.php
│   ├── Views/
│   │   ├── auth/
│   │   │   └── dashboard.php (Updated with materials display)
│   │   └── materials/
│   │       └── upload.php (New)
│   ├── Database/
│   │   └── Migrations/
│   │       └── 2025-10-23-161710_CreateMaterialsTable.php
│   └── Config/
│       └── Routes.php (Updated with material routes)
└── writable/
    └── uploads/
        └── materials/ (Uploaded files stored here)
            ├── index.html (Security)
            └── .htaccess (Security - created)
```

## 🔒 Security Features Implemented

1. **Authentication**: All material operations require login
2. **Authorization**: Role-based access (admin/teacher for upload, enrolled students for download)
3. **File Validation**: Type and size checks
4. **Secure Storage**: Files stored outside public directory
5. **Random Filenames**: Prevents direct guessing of file paths
6. **CSRF Protection**: Forms include CSRF tokens
7. **Direct Access Prevention**: .htaccess rules block direct file access
8. **Enrollment Verification**: Students can only download from enrolled courses

## 🚀 Quick Start Commands

```bash
# Start the development server
php spark serve

# Check migration status
php spark migrate:status

# Run migrations (if needed)
php spark migrate

# Rollback migrations (if needed)
php spark migrate:rollback
```

## 📊 Database Verification

You can verify the materials table in your database:

```sql
-- View table structure
DESCRIBE materials;

-- View all materials
SELECT * FROM materials;

-- View materials with course information
SELECT m.*, c.title as course_title 
FROM materials m 
JOIN courses c ON m.course_id = c.id;
```

## 🎯 Expected Workflow

### For Admin/Teacher:
1. Login → Dashboard
2. View courses with "Upload Materials" button
3. Click button → Upload form
4. Select file → Upload
5. File saved + Database record created
6. View/Delete materials

### For Students:
1. Login → Dashboard
2. View enrolled courses
3. See materials listed under each course
4. Click download button
5. File downloads securely

## ✨ Additional Features

1. **Material Count Display**: Shows number of materials per course
2. **Upload Date Display**: Shows when each material was uploaded
3. **File Type Icons**: Visual indicators for different file types
4. **Responsive Design**: Works on mobile and desktop
5. **Flash Messages**: User-friendly feedback for all operations
6. **Confirmation Dialogs**: Prevents accidental deletions

## 🐛 Troubleshooting

### Issue: Upload fails
- Check if `writable/uploads/materials/` directory exists and is writable
- Verify file size is under 10MB
- Ensure file type is allowed (PDF, PPT, PPTX, DOC, DOCX)

### Issue: Download doesn't work
- Verify user is enrolled in the course
- Check if file exists in `writable/uploads/materials/`
- Ensure user is logged in

### Issue: Access denied
- Verify user role (admin/teacher for upload, student for download)
- Check if user is enrolled in the course (for downloads)

## 📝 Notes

- All files are stored with random names for security
- Original filenames are preserved in the database
- Files are automatically deleted when material record is removed
- System supports multiple materials per course
- Download count and analytics can be added in future enhancements

## ✅ Completion Checklist

- [x] Migration created and run
- [x] MaterialModel created with all required methods
- [x] Materials controller created with upload, delete, download methods
- [x] File upload functionality implemented with validation
- [x] Upload view created with Bootstrap styling
- [x] Student dashboard updated to show materials
- [x] Download method with enrollment verification
- [x] Routes configured correctly
- [x] Security measures implemented
- [x] Testing guide created

---

**Status**: ✅ **FULLY IMPLEMENTED AND READY FOR TESTING**

All requirements from the laboratory activity have been successfully implemented in your system. The materials management feature is now fully integrated and ready to use!
