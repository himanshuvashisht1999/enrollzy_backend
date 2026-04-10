<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpertController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\OrganisationController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HeroSliderController;
use App\Http\Controllers\Admin\VideoTestimonialController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NoteworthyCategoryController;
use App\Http\Controllers\Admin\NoteworthyMentionController;
use App\Http\Controllers\Admin\OrganisationCourseController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CommunityCategoryController;
use App\Http\Controllers\Admin\CommunityQuestionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ExamStageDataController;

// ✅ Root Redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// ✅ Admin Auth Routes
Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ✅ Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Categories
    Route::resource('/admin/categories', CategoryController::class);

    // Students
    Route::resource('/admin/students', StudentController::class);

    // Experts
    Route::resource('/admin/experts', ExpertController::class)->names([
        'index' => 'experts.index',
        'create' => 'experts.create',
        'store' => 'experts.store',
        'edit' => 'experts.edit',
        'update' => 'experts.update',
        'destroy' => 'experts.destroy',
    ]);
    Route::post('/admin/experts/{expert}/commission', [ExpertController::class, 'updateCommission'])->name('experts.commission.update');
    Route::resource('/admin/expert-categories', \App\Http\Controllers\Admin\ExpertCategoryController::class)->names('expert-categories');

    // Expert Bookings
    Route::get('/admin/expert-bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('admin.bookings.index');
    Route::resource('/admin/expert-categories', \App\Http\Controllers\Admin\ExpertCategoryController::class)->names('expert-categories');

    // Alumni
    Route::resource('/admin/alumni', \App\Http\Controllers\Admin\AlumniController::class)->names([
        'index' => 'admin.alumni.index',
        'create' => 'admin.alumni.create',
        'store' => 'admin.alumni.store',
        'edit' => 'admin.alumni.edit',
        'update' => 'admin.alumni.update',
        'destroy' => 'admin.alumni.destroy',
    ]);

    // Blogs
    Route::resource('/admin/blogs', BlogController::class)->names([
        'index' => 'blogs.index',
        'create' => 'blogs.create',
        'store' => 'blogs.store',
        'edit' => 'blogs.edit',
        'update' => 'blogs.update',
        'destroy' => 'blogs.destroy',
    ]);

    // FAQs
    Route::resource('/admin/faqs', FaqController::class)->names([
        'index' => 'faqs.index',
        'create' => 'faqs.create',
        'store' => 'faqs.store',
        'edit' => 'faqs.edit',
        'update' => 'faqs.update',
        'destroy' => 'faqs.destroy',
    ]);

    // Testimonials
    Route::resource('/admin/testimonials', TestimonialController::class)->names([
        'index' => 'testimonials.index',
        'create' => 'testimonials.create',
        'store' => 'testimonials.store',
        'edit' => 'testimonials.edit',
        'update' => 'testimonials.update',
        'destroy' => 'testimonials.destroy',
    ]);

    // Organisations
    Route::resource('/admin/organisations', OrganisationController::class)->names([
        'index' => 'admin.organisations.index',
        'create' => 'admin.organisations.create',
        'store' => 'admin.organisations.store',
        'edit' => 'admin.organisations.edit',
        'update' => 'admin.organisations.update',
        'destroy' => 'admin.organisations.destroy',
    ]);
    Route::post('/admin/organisations/store-draft', [OrganisationController::class, 'storeDraft'])->name('admin.organisations.store-draft');
    Route::post('/admin/organisations/{organisation}/autosave', [OrganisationController::class, 'autosave'])->name('admin.organisations.autosave');
    Route::post('/admin/organisations/{organisation}/autosave-repeater', [OrganisationController::class, 'autosaveRepeater'])->name('admin.organisations.autosave-repeater');
    Route::get('/admin/organisations/{id}/campuses-json', [OrganisationController::class, 'getCampusesJson'])->name('admin.organisations.get-campuses-json');

    // Campuses (Nested under Organisations)
    Route::resource('/admin/organisations.campuses', \App\Http\Controllers\Admin\CampusController::class)->names([
        'index' => 'admin.organisations.campuses.index',
        'create' => 'admin.organisations.campuses.create',
        'store' => 'admin.organisations.campuses.store',
        'edit' => 'admin.organisations.campuses.edit',
        'update' => 'admin.organisations.campuses.update',
        'destroy' => 'admin.organisations.campuses.destroy',
    ]);
    Route::post('/admin/organisations/{organisation}/campuses/store-draft', [\App\Http\Controllers\Admin\CampusController::class, 'storeDraft'])->name('admin.organisations.campuses.store-draft');
    Route::post('/admin/organisations/{organisation}/campuses/{campus}/autosave-tab', [\App\Http\Controllers\Admin\CampusController::class, 'autosaveTab'])->name('admin.organisations.campuses.autosave-tab');

    // Departments (Nested under Campuses contextually, but flat resource for now)
    Route::resource('/admin/departments', DepartmentController::class)->names('admin.departments');
    Route::post('/admin/departments/store-draft', [DepartmentController::class, 'storeDraft'])->name('admin.departments.store-draft');
    Route::post('/admin/departments/{department}/autosave-tab', [DepartmentController::class, 'autosaveTab'])->name('admin.departments.autosave-tab');

    // Exams
    Route::resource('/admin/exams', \App\Http\Controllers\Admin\ExamController::class)->names('admin.exams');

    // Exam Stage Data Management
    Route::get('/admin/exams/{exam}/stages/{stage}/edit', [ExamStageDataController::class, 'edit'])->name('admin.exams.stages.edit');
    Route::post('/admin/exams/{exam}/interview/update', [ExamStageDataController::class, 'updateInterview'])->name('admin.exams.interview.update');
    Route::post('/admin/exams/{exam}/skill/update', [ExamStageDataController::class, 'updateSkill'])->name('admin.exams.skill.update');
    Route::post('/admin/exams/{exam}/medical/update', [ExamStageDataController::class, 'updateMedical'])->name('admin.exams.medical.update');
    Route::post('/admin/exams/{exam}/preliminary/update', [ExamStageDataController::class, 'updatePreliminary'])->name('admin.exams.preliminary.update');
    Route::post('/admin/exams/{exam}/main/update', [ExamStageDataController::class, 'updateMain'])->name('admin.exams.main.update');

    // Exam Subjects
    Route::resource('/admin/exam-subjects', \App\Http\Controllers\Admin\ExamSubjectController::class)->names('admin.exam-subjects');

    // Counsellings (Nested under Exams)
    Route::resource('/admin/exams.counsellings', \App\Http\Controllers\Admin\CounsellingController::class)->names([
        'index' => 'admin.exams.counsellings.index',
        'create' => 'admin.exams.counsellings.create',
        'store' => 'admin.exams.counsellings.store',
        'edit' => 'admin.exams.counsellings.edit',
        'update' => 'admin.exams.counsellings.update',
        'destroy' => 'admin.exams.counsellings.destroy',
    ]);
    Route::post('/admin/exams/{exam}/counsellings/store-draft', [\App\Http\Controllers\Admin\CounsellingController::class, 'storeDraft'])->name('admin.exams.counsellings.store-draft');
    Route::post('/admin/exams/{exam}/counsellings/{counselling}/autosave', [\App\Http\Controllers\Admin\CounsellingController::class, 'autosave'])->name('admin.exams.counsellings.autosave');
    Route::post('/admin/exams/{exam}/counsellings/{counselling}/autosave-tab', [\App\Http\Controllers\Admin\CounsellingController::class, 'autosaveTab'])->name('admin.exams.counsellings.autosave-tab');


    Route::get('/admin/get-sub-types', [OrganisationController::class, 'getSubTypes'])->name('admin.organisations.get-sub-types');

    // Leads
    Route::get('/admin/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::patch('/admin/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
    Route::delete('/admin/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings/update', [SettingController::class, 'update'])->name('admin.settings.update');

    // Commission Settings
    Route::get('/admin/commission', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commission.index');
    Route::post('/admin/commission/global', [\App\Http\Controllers\Admin\CommissionController::class, 'updateGlobal'])->name('admin.commission.global.update');
    Route::post('/admin/commission/category', [\App\Http\Controllers\Admin\CommissionController::class, 'updateCategory'])->name('admin.commission.category.update');

    // Hero Sliders
    Route::resource('/admin/hero-sliders', HeroSliderController::class)->names('admin.hero-sliders');

    // Specialized Courses (Home Services)
    Route::resource('/admin/home-services', \App\Http\Controllers\Admin\HomeServiceController::class)->names('admin.home-services');

    // Benefits (Why Choose Us)
    Route::resource('/admin/home-benefits', \App\Http\Controllers\Admin\HomeBenefitController::class)->names('admin.home-benefits');

    // Video Testimonials
    Route::resource('/admin/video-testimonials', VideoTestimonialController::class)->names('admin.video-testimonials');

    // Noteworthy Categories
    Route::resource('/admin/noteworthy-categories', NoteworthyCategoryController::class)->names('admin.noteworthy-categories');

    // Noteworthy Mentions
    Route::resource('/admin/noteworthy-mentions', NoteworthyMentionController::class)->names('admin.noteworthy-mentions');

    // Organisation Courses
    Route::resource('/admin/organisation-courses', OrganisationCourseController::class)->names([
        'index' => 'admin.organisation-courses.index',
        'create' => 'admin.organisation-courses.create',
        'store' => 'admin.organisation-courses.store',
        'edit' => 'admin.organisation-courses.edit',
        'update' => 'admin.organisation-courses.update',
        'destroy' => 'admin.organisation-courses.destroy',

        'school-store' => 'admin.organisation-courses.schoolStore',
    ]);
    Route::get('/admin/organisation-courses/{id}/duplicate', [OrganisationCourseController::class, 'duplicate'])
        ->name('admin.organisation-courses.duplicate');
    Route::post(
        'admin/organisation-courses/school-store',
        [OrganisationCourseController::class, 'schoolStore']
    )->name('admin.organisation-courses.school-store');
    Route::get('organisation-school/{id}/edit', [OrganisationCourseController::class, 'schoolEdit'])
        ->name('admin.organisation-school.edit');

    Route::put('organisation-school/{id}', [OrganisationCourseController::class, 'schoolUpdate'])
        ->name('admin.organisation-school.update');

    Route::delete('organisation-school/{id}', [OrganisationCourseController::class, 'schoolDestroy'])
        ->name('admin.organisation-school.destroy');

    Route::post('/admin/organisation-courses/store-draft', [OrganisationCourseController::class, 'storeDraft'])->name('admin.organisation-courses.store-draft');
    Route::post('/admin/organisation-courses/{organisation}/courses/{course}/autosave-tab', [OrganisationCourseController::class, 'autosaveTab'])->name('admin.organisation-courses.autosave-tab');

    // Master Course Management
    Route::resource('/admin/courses', CourseController::class)->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);
    Route::get('admin/courses/{course}/duplicate', [CourseController::class, 'duplicate'])
        ->name('admin.courses.duplicate');

    // Community Management
    Route::resource('/admin/community-categories', CommunityCategoryController::class)->names('admin.community-categories');
    Route::resource('/admin/community-questions', CommunityQuestionController::class)->names('admin.community-questions');
    Route::patch('/admin/community-questions/{question}/toggle-verify', [CommunityQuestionController::class, 'toggleVerify'])->name('admin.community-questions.toggle-verify');

    // Homepage Sections
    Route::get('homepage-sections', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'index'])->name('homepage-sections.index');
    Route::patch('homepage-sections/{homepageSection}', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'update'])->name('homepage-sections.update');
    Route::post('homepage-sections/order', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'updateOrder'])->name('homepage-sections.update-order');

    // Master Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('program-levels', \App\Http\Controllers\Admin\ProgramLevelController::class);
        Route::resource('program-types', \App\Http\Controllers\Admin\ProgramTypeController::class);
        Route::resource('stream-offereds', \App\Http\Controllers\Admin\StreamOfferedController::class);
        Route::resource('disciplines', \App\Http\Controllers\Admin\DisciplineController::class);
        Route::resource('specializations', \App\Http\Controllers\Admin\SpecializationController::class);
        Route::resource('organisation-types', \App\Http\Controllers\Admin\OrganisationTypeController::class);
        Route::resource('accreditation-approvals', \App\Http\Controllers\Admin\AccreditationApprovalController::class);
        Route::resource('campus-types', \App\Http\Controllers\Admin\CampusTypeController::class);
        Route::resource('sports', \App\Http\Controllers\Admin\SportController::class); // Master sport route
        Route::resource('organisation-sub-types', \App\Http\Controllers\Admin\OrganisationSubTypeController::class);
        Route::resource('languages', \App\Http\Controllers\Admin\LanguageController::class);
        Route::resource('exam-stages', \App\Http\Controllers\Admin\ExamStageController::class);
        Route::resource('caste-categories', \App\Http\Controllers\Admin\CasteCategoryController::class);

        // Organisation Specific Sub-routes
        Route::delete('organisation-awards/{id}', [\App\Http\Controllers\Admin\OrganisationController::class, 'deleteAward'])->name('organisation-awards.destroy');
        Route::delete('organisation-sports/{id}', [\App\Http\Controllers\Admin\OrganisationController::class, 'deleteSport'])->name('organisation-sports.destroy');
    });
});



