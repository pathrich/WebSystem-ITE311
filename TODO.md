# TODO: Implement Materials Management System

## Step 1: Create Database Migration for Materials Table
- [x] Create migration file: php spark make:migration CreateMaterialsTable
- [x] Define table with fields: id (PK, auto-increment), course_id (INT, FK to courses), file_name (VARCHAR(255)), file_path (VARCHAR(255)), created_at (DATETIME)
- [x] Run migration: php spark migrate

## Step 2: Create MaterialModel
- [x] Create app/Models/MaterialModel.php
- [x] Add insertMaterial($data) method
- [x] Add getMaterialsByCourse($course_id) method

## Step 3: Create Materials Controller
- [x] Create app/Controllers/Materials.php
- [x] Add upload($course_id) method for file upload form and handling
- [x] Add delete($material_id) method for deletion
- [x] Add download($material_id) method for secure download

## Step 4: Implement File Upload Functionality
- [x] Load File Uploading and Validation libraries in upload method
- [x] Configure upload preferences (path: writable/uploads/materials, allowed types: pdf,ppt,doc,docx, max size)
- [x] Handle POST request, perform upload, save to DB
- [x] Set flash messages and redirect

## Step 5: Create File Upload View
- [x] Create app/Views/materials/upload.php
- [x] Form with enctype="multipart/form-data", file input, Bootstrap styling

## Step 6: Display Downloadable Materials for Students
- [x] Modify student dashboard or course view to show materials
- [x] Use MaterialModel to fetch materials for enrolled courses
- [x] List materials with download links

## Step 7: Implement Download Method
- [x] Check user login and enrollment in download method
- [x] Retrieve file path, use Response class for secure download

## Step 8: Update Routes
- [x] Add routes in app/Config/Routes.php:
  - $routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
  - $routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
  - $routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
  - $routes->get('/materials/download/(:num)', 'Materials::download/$1');

## Step 9: Test the Application
- [x] Test upload as admin/teacher
- [x] Test download as enrolled student
- [x] Test restrictions for non-enrolled users
- [x] Ensure files are saved correctly and DB records added
