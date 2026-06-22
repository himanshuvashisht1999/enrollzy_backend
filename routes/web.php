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
use App\Http\Controllers\Admin\AboutUsController;
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
use App\Http\Controllers\Admin\Hr\WhatsappTemplateController;
use App\Http\Controllers\Admin\Hr\LeadSourceController;
use App\Http\Controllers\Admin\Hr\ClientController;
use App\Http\Controllers\Admin\Hr\ProjectCategoryController;
use App\Http\Controllers\Admin\Hr\ProjectsController;
use App\Http\Controllers\Admin\Hr\MilestoneController;
use App\Http\Controllers\Admin\Hr\TaskController;
use App\Http\Controllers\Admin\Hr\TaskCommentController;
use App\Http\Controllers\Admin\Hr\CustomerController;
use App\Http\Controllers\Admin\Hr\CustomerCategoryController;
use App\Http\Controllers\Admin\Hr\CustomerFieldController;
use App\Http\Controllers\Admin\Hr\InstituteController;
use App\Http\Controllers\Admin\Hr\CallingStatusController;
use App\Http\Controllers\Admin\Hr\CallingActionController;
use App\Http\Controllers\Admin\Hr\CallingController;
use App\Http\Controllers\Admin\Hr\ClockController;
use App\Http\Controllers\Admin\Hr\InterestedInController;
use App\Http\Controllers\Admin\Hr\CustomerSessionController;

// ✅ Root Redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// ✅ Admin Auth Routes
Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ✅ Admin Routes
Route::middleware(['auth:admin,web', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Categories
    Route::resource('/admin/categories', CategoryController::class);
    
    // Career Roadmap
    Route::resource('/admin/career-roadmap-categories', \App\Http\Controllers\Admin\CareerRoadmapCategoryController::class)->names('admin.career-roadmap-categories');
    Route::resource('/admin/career-roadmap-stages', \App\Http\Controllers\Admin\CareerRoadmapStageController::class)->names('admin.career-roadmap-stages');
    Route::resource('/admin/career-roadmap-sub-modules', \App\Http\Controllers\Admin\CareerRoadmapSubModuleController::class)->names('admin.career-roadmap-sub-modules');

    // FAQs
    Route::resource('/admin/faq-categories', \App\Http\Controllers\Admin\FaqCategoryController::class)->names('admin.faq-categories');
    Route::resource('/admin/faq-items', \App\Http\Controllers\Admin\FaqItemController::class)->names('admin.faq-items');

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
    Route::patch('/admin/organisations/{organisation}/toggle-status', [OrganisationController::class, 'toggleStatus'])->name('admin.organisations.toggle-status');
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

    // Dynamic Exams
    Route::resource('/admin/dynamic-exams', \App\Http\Controllers\Admin\DynamicExamController::class)->names('admin.dynamic-exams');
    Route::get('/admin/dynamic-exams/{dynamicExam}/data', [\App\Http\Controllers\Admin\DynamicExamController::class, 'data'])->name('admin.dynamic-exams.data');
    Route::post('/admin/dynamic-exams/{dynamicExam}/data', [\App\Http\Controllers\Admin\DynamicExamController::class, 'saveData'])->name('admin.dynamic-exams.data.save');
    Route::post('/admin/dynamic-exams/{dynamicExam}/autosave-tab', [\App\Http\Controllers\Admin\DynamicExamController::class, 'autosaveTab'])->name('admin.dynamic-exams.autosave-tab');

    // Dynamic Exams - Counselling
    Route::resource('/admin/dynamic-exams.counsellings', \App\Http\Controllers\Admin\DynamicCounsellingController::class)->names([
        'index'   => 'admin.dynamic-exams.counsellings.index',
        'create'  => 'admin.dynamic-exams.counsellings.create',
        'store'   => 'admin.dynamic-exams.counsellings.store',
        'edit'    => 'admin.dynamic-exams.counsellings.edit',
        'update'  => 'admin.dynamic-exams.counsellings.update',
        'destroy' => 'admin.dynamic-exams.counsellings.destroy',
    ])->except(['show']);
    Route::get('/admin/dynamic-exams/{dynamicExam}/counsellings/{counselling}/data', [\App\Http\Controllers\Admin\DynamicCounsellingController::class, 'data'])->name('admin.dynamic-exams.counsellings.data');
    Route::post('/admin/dynamic-exams/{dynamicExam}/counsellings/{counselling}/data', [\App\Http\Controllers\Admin\DynamicCounsellingController::class, 'saveData'])->name('admin.dynamic-exams.counsellings.data.save');
    Route::post('/admin/dynamic-exams/{dynamicExam}/counsellings/store-draft', [\App\Http\Controllers\Admin\DynamicCounsellingController::class, 'storeDraft'])->name('admin.dynamic-exams.counsellings.store-draft');
    Route::post('/admin/dynamic-exams/{dynamicExam}/counsellings/{counselling}/autosave-tab', [\App\Http\Controllers\Admin\DynamicCounsellingController::class, 'autosaveTab'])->name('admin.dynamic-exams.counsellings.autosave-tab');


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

    // SEO Organization Settings
    Route::get('/admin/seo-organization', [\App\Http\Controllers\Admin\SeoOrganizationSettingController::class, 'edit'])->name('admin.seo_organization.edit');
    Route::post('/admin/seo-organization', [\App\Http\Controllers\Admin\SeoOrganizationSettingController::class, 'update'])->name('admin.seo_organization.update');

    // Homepage SEO
    Route::get('/admin/seo-homepage', [\App\Http\Controllers\Admin\SeoHomepageController::class, 'edit'])->name('admin.seo_homepage.edit');
    Route::post('/admin/seo-homepage', [\App\Http\Controllers\Admin\SeoHomepageController::class, 'update'])->name('admin.seo_homepage.update');

    // Global SEO Defaults
    Route::get('/admin/seo-defaults', [\App\Http\Controllers\Admin\SeoDefaultController::class, 'edit'])->name('admin.seo_defaults.edit');
    Route::post('/admin/seo-defaults', [\App\Http\Controllers\Admin\SeoDefaultController::class, 'update'])->name('admin.seo_defaults.update');

    // About Us Page
    Route::get('/admin/about-us', [AboutUsController::class, 'edit'])->name('admin.about_us.edit');
    Route::post('/admin/about-us/update', [AboutUsController::class, 'update'])->name('admin.about_us.update');
    Route::post('/admin/about-us/offers', [AboutUsController::class, 'storeOffer'])->name('admin.about_us.offers.store');
    Route::post('/admin/about-us/offers/{id}', [AboutUsController::class, 'updateOffer'])->name('admin.about_us.offers.update');
    Route::delete('/admin/about-us/offers/{id}', [AboutUsController::class, 'destroyOffer'])->name('admin.about_us.offers.destroy');
    Route::post('/admin/about-us/features', [AboutUsController::class, 'storeFeature'])->name('admin.about_us.features.store');
    Route::post('/admin/about-us/features/{id}', [AboutUsController::class, 'updateFeature'])->name('admin.about_us.features.update');
    Route::delete('/admin/about-us/features/{id}', [AboutUsController::class, 'destroyFeature'])->name('admin.about_us.features.destroy');
    Route::post('/admin/about-us/impacts', [AboutUsController::class, 'storeImpact'])->name('admin.about_us.impacts.store');
    Route::post('/admin/about-us/impacts/{id}', [AboutUsController::class, 'updateImpact'])->name('admin.about_us.impacts.update');
    Route::delete('/admin/about-us/impacts/{id}', [AboutUsController::class, 'destroyImpact'])->name('admin.about_us.impacts.destroy');

    // Commission Settings
    Route::get('/admin/commission', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commission.index');
    Route::post('/admin/commission/global', [\App\Http\Controllers\Admin\CommissionController::class, 'updateGlobal'])->name('admin.commission.global.update');
    Route::post('/admin/commission/category', [\App\Http\Controllers\Admin\CommissionController::class, 'updateCategory'])->name('admin.commission.category.update');

    // Hero Sliders
    Route::resource('/admin/hero-sliders', HeroSliderController::class)->names('admin.hero-sliders');
    Route::post('/admin/hero-sliders/{hero_slider}/toggle-status', [HeroSliderController::class, 'updateStatus'])->name('admin.hero-sliders.toggle-status');
    Route::post('/admin/hero-sliders/global-banner-toggle', [HeroSliderController::class, 'toggleGlobalBanner'])->name('admin.hero-sliders.global-banner-toggle');

    // Specialized Courses (Home Services)
    Route::resource('/admin/home-services', \App\Http\Controllers\Admin\HomeServiceController::class)->names('admin.home-services');

    // Benefits (Why Choose Us)
    Route::resource('/admin/home-benefits', \App\Http\Controllers\Admin\HomeBenefitController::class)->names('admin.home-benefits');

    // Trending Skills
    Route::resource('/admin/trending-skills', \App\Http\Controllers\Admin\TrendingSkillController::class)->names('admin.trending-skills');
    
    // Dynamic Pages
    Route::resource('/admin/pages', \App\Http\Controllers\Admin\PageController::class)->names('admin.pages');

    // Company Marquee
    Route::post('/admin/school-marquees/update-direction', [\App\Http\Controllers\Admin\SchoolMarqueeController::class, 'updateDirection'])->name('admin.school-marquees.update-direction');
    Route::resource('/admin/school-marquees', \App\Http\Controllers\Admin\SchoolMarqueeController::class)->names('admin.school-marquees');
    Route::post('/admin/school-marquees/{school_marquee}/toggle-status', [\App\Http\Controllers\Admin\SchoolMarqueeController::class, 'toggleStatus'])->name('admin.school-marquees.toggle-status');

    // Institute Marquee
    Route::post('/admin/institute-marquees/update-direction', [\App\Http\Controllers\Admin\InstituteMarqueeController::class, 'updateDirection'])->name('admin.institute-marquees.update-direction');
    Route::resource('/admin/institute-marquees', \App\Http\Controllers\Admin\InstituteMarqueeController::class)->names('admin.institute-marquees');
    Route::post('/admin/institute-marquees/{institute_marquee}/toggle-status', [\App\Http\Controllers\Admin\InstituteMarqueeController::class, 'toggleStatus'])->name('admin.institute-marquees.toggle-status');

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

    Route::resource('/admin/community-replies', \App\Http\Controllers\Admin\CommunityReplyController::class)->names('admin.community-replies');
    Route::patch('/admin/community-replies/{reply}/toggle-active', [\App\Http\Controllers\Admin\CommunityReplyController::class, 'toggleActive'])->name('admin.community-replies.toggle-active');

    // Header Links
    Route::resource('header-links', \App\Http\Controllers\Admin\HeaderLinkController::class)->names('admin.header-links');

    // Main Header Menus
    Route::resource('header-menus', \App\Http\Controllers\Admin\HeaderMenuController::class)->names('admin.header-menus');

    // Footer Setup
    Route::get('footer-setup', [\App\Http\Controllers\Admin\FooterSetupController::class, 'index'])->name('admin.footer-setup.index');
    Route::post('footer-setup/settings', [\App\Http\Controllers\Admin\FooterSetupController::class, 'updateSettings'])->name('admin.footer-setup.update-settings');
    Route::get('footer-setup/create', [\App\Http\Controllers\Admin\FooterSetupController::class, 'createMenu'])->name('admin.footer-setup.create');
    Route::post('footer-setup', [\App\Http\Controllers\Admin\FooterSetupController::class, 'storeMenu'])->name('admin.footer-setup.store');
    Route::get('footer-setup/{footerMenu}/edit', [\App\Http\Controllers\Admin\FooterSetupController::class, 'editMenu'])->name('admin.footer-setup.edit');
    Route::put('footer-setup/{footerMenu}', [\App\Http\Controllers\Admin\FooterSetupController::class, 'updateMenu'])->name('admin.footer-setup.update');
    Route::delete('footer-setup/{footerMenu}', [\App\Http\Controllers\Admin\FooterSetupController::class, 'destroyMenu'])->name('admin.footer-setup.destroy');

    // General Links in Footer
    Route::post('footer-setup/general-links', [\App\Http\Controllers\Admin\FooterSetupController::class, 'storeGeneralLink'])->name('admin.footer-setup.general-links.store');
    Route::put('footer-setup/general-links/{generalLink}', [\App\Http\Controllers\Admin\FooterSetupController::class, 'updateGeneralLink'])->name('admin.footer-setup.general-links.update');
    Route::delete('footer-setup/general-links/{generalLink}', [\App\Http\Controllers\Admin\FooterSetupController::class, 'destroyGeneralLink'])->name('admin.footer-setup.general-links.destroy');

    // Homepage Sections
    Route::get('homepage-sections', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'index'])->name('homepage-sections.index');
    Route::get('homepage-sections/{homepageSection}/edit', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'edit'])->name('homepage-sections.edit');
    Route::put('homepage-sections/{homepageSection}/details', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'updateDetails'])->name('homepage-sections.update-details');
    Route::patch('homepage-sections/{homepageSection}', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'update'])->name('homepage-sections.update');
    Route::post('homepage-sections/order', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'updateOrder'])->name('homepage-sections.update-order');

    // Master Management
    Route::prefix('masters')->group(function () {
        Route::resource('facilities', \App\Http\Controllers\Admin\FacilityController::class)->names('admin.facilities')->except(['create', 'show', 'edit']);
        Route::resource('sports', \App\Http\Controllers\Admin\SportController::class); // Master sport route
    });
    
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

        // HR Leaves Module
        Route::prefix('hr')->name('hr.')->group(function () {
            Route::resource('leave-settings', \App\Http\Controllers\Admin\Hr\LeaveSettingController::class);
            Route::resource('leave-policies', \App\Http\Controllers\Admin\Hr\LeavePolicyController::class);
            Route::resource('holidays', \App\Http\Controllers\Admin\Hr\HolidayController::class);
            Route::resource('leaves', \App\Http\Controllers\Admin\Hr\LeavesController::class);

            // Staff Module Routes
            Route::resource('departments', \App\Http\Controllers\Admin\Hr\DepartmentController::class);
            Route::resource('designations', \App\Http\Controllers\Admin\Hr\DesignationController::class);
            Route::resource('staff', \App\Http\Controllers\Admin\Hr\StaffController::class);
            Route::resource('roles', \App\Http\Controllers\Admin\Hr\RoleController::class);
            Route::resource('banks', \App\Http\Controllers\Admin\Hr\BanksController::class);
            Route::post('change-staff-role', [\App\Http\Controllers\Admin\Hr\StaffController::class, 'changeStaffRole'])->name('change-staff-role');

            // Salary Module Routes
            Route::get('attendance', [\App\Http\Controllers\Admin\Hr\AttendanceController::class, 'showAttendance'])->name('attendance.index');
            Route::post('get-at-details', [\App\Http\Controllers\Admin\Hr\AttendanceController::class, 'getAtDetailsForDay'])->name('attendance.details');
            
            Route::resource('advance', \App\Http\Controllers\Admin\Hr\AdvancePayController::class);
            Route::post('advance/bonus', [\App\Http\Controllers\Admin\Hr\AdvancePayController::class, 'storeBonus'])->name('advance.bonus.store');
            Route::post('get-advance-amount', [\App\Http\Controllers\Admin\Hr\AdvancePayController::class, 'getAdvancePayAmount'])->name('advance.get-amount');

            Route::get('payroll', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'showPayroll'])->name('payroll.index');
            Route::post('payroll/calculate', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'calculateMonthWiseSalary'])->name('payroll.calculate');
            Route::post('payroll/make-payment', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'makeEmployeePayment'])->name('payroll.make-payment');

            Route::resource('payout', \App\Http\Controllers\Admin\Hr\PayoutController::class);

            // Whatsapp Template Module Routes
            Route::resource('whatsapp_template', \App\Http\Controllers\Admin\Hr\WhatsappTemplateController::class);
            Route::controller(\App\Http\Controllers\Admin\Hr\WhatsappTemplateController::class)->group(function () {
                Route::get('whatsapp_template/send-message/{id}', 'sendMessage')->name('whatsapp_template.sendMessage');
                Route::post('whatsapp_template/post-send-message', 'postSendMessage')->name('whatsapp_template.postSendMessage');
                Route::get('whatsapp_report', 'report')->name('whatsapp_template.report');
                Route::get('whatsapp_stop', 'whatsappStop')->name('whatsapp_template.whatsappStop');
                Route::post('getCategoryNumbers', 'getCategoryNumbers')->name('whatsapp_template.getCategoryNumbers');
            });
            
            // AJAX Routes
            Route::post('get-designations', [\App\Http\Controllers\Admin\Hr\HrAjaxController::class, 'getDesignations'])->name('get-designations');
            Route::post('get-users', [\App\Http\Controllers\Admin\Hr\HrAjaxController::class, 'getUsers'])->name('get-users');
            // Project & Tasks Module Routes
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::resource('lead-sources', LeadSourceController::class);
                Route::resource('clients', ClientController::class);
                Route::resource('project-categories', ProjectCategoryController::class);
                Route::resource('index', ProjectsController::class); // Main project list
                Route::resource('milestones', MilestoneController::class);
                Route::resource('tasks', TaskController::class);
                Route::resource('comments', TaskCommentController::class);
                Route::post('get-project-data', [TaskController::class, 'getProjectData'])->name('get-data');
            });

            // Clock / Attendance Routes
            Route::prefix('clock')->name('clock.')->group(function() {
                Route::post('check-in', [ClockController::class, 'checkInAttendance'])->name('check_in');
                Route::post('check-out', [ClockController::class, 'checkOutAttendance'])->name('check_out');
                Route::post('start-break', [ClockController::class, 'startBreakTime'])->name('start_break');
                Route::post('end-break', [ClockController::class, 'endLunchBreak'])->name('end_break');
                Route::post('end-lunch-break', [ClockController::class, 'endLunchBreak'])->name('end_lunchBreak');
            });

        });

        // Customer Management Module Routes
        Route::prefix('customers')->name('customers.main.')->group(function () {
            Route::resource('index', CustomerController::class);
            Route::post('get-sub-categories', [CustomerController::class, 'getCategories'])->name('get-sub-categories');
        });

        Route::resource('customer-categories', CustomerCategoryController::class)->names('customer-categories');
        Route::post('quick-add-category', [CustomerCategoryController::class, 'quickStore'])->name('quick-add-category');
        Route::post('quick-add-interest', [InterestedInController::class, 'quickStore'])->name('quick-add-interest');
        Route::post('quick-add-session', [CustomerSessionController::class, 'quickStore'])->name('quick-add-session');
        Route::resource('customer-fields', CustomerFieldController::class)->names('customer-fields');
        Route::resource('institutes', InstituteController::class)->names('institutes');
        Route::resource('interested-ins', InterestedInController::class)->names('interested-ins');
        Route::resource('customer-sessions', CustomerSessionController::class)->names('customer-sessions');

        // Student CRM & Calling Module Routes
        Route::prefix('students-crm')->name('students-crm.')->group(function () {
            Route::resource('calling-statuses', CallingStatusController::class);
            Route::resource('calling-actions', CallingActionController::class);
            Route::get('calling-module', [CallingController::class, 'index'])->name('calling-module.index');
            Route::post('calling-module', [CallingController::class, 'store'])->name('calling-module.store');
            Route::get('calling-history', [CallingController::class, 'history'])->name('calling-history.index');
        });

        // Consultant Management Module Routes
        Route::prefix('consultants')->name('consultants.')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'create'])->name('create');
            Route::post('store', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'store'])->name('store');
            Route::get('edit/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'edit'])->name('edit');
            Route::put('update/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'update'])->name('update');
            Route::get('sub-categories', [\App\Http\Controllers\Admin\Hr\ConsultantController::class, 'getSubCategories'])->name('sub-categories');
        });

        Route::prefix('consultant-categories')->name('consultant-categories.')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\Hr\ConsultantCategoryController::class, 'index'])->name('index');
            Route::post('store', [\App\Http\Controllers\Admin\Hr\ConsultantCategoryController::class, 'store'])->name('store');
            Route::delete('destroy/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('consultant-settings')->name('consultant-settings.')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'index'])->name('index');
            Route::post('store-type', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'storeType'])->name('store-type');
            Route::post('store-status', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'storeStatus'])->name('store-status');
            Route::post('store-access-level', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'storeAccessLevel'])->name('store-access-level');
            Route::post('store-visibility', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'storeLeadVisibility'])->name('store-visibility');
            
            Route::put('update-type/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'updateType'])->name('update-type');
            Route::put('update-status/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'updateStatus'])->name('update-status');
            Route::put('update-access-level/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'updateAccessLevel'])->name('update-access-level');
            Route::put('update-visibility/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'updateLeadVisibility'])->name('update-visibility');

            Route::delete('destroy-type/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'destroyType'])->name('destroy-type');
            Route::delete('destroy-status/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'destroyStatus'])->name('destroy-status');
            Route::delete('destroy-access-level/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'destroyAccessLevel'])->name('destroy-access-level');
            Route::delete('destroy-visibility/{id}', [\App\Http\Controllers\Admin\Hr\ConsultantMasterController::class, 'destroyLeadVisibility'])->name('destroy-visibility');
        });
    });
});
