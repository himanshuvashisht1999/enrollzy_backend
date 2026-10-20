<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpertController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\OrganisationController;
use App\Http\Controllers\Admin\AcademicDirectoryController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HeroSliderController;
use App\Http\Controllers\Admin\MegaMenuController;
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
use App\Http\Controllers\Admin\ContactUsDetailController;
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
use App\Http\Controllers\Admin\ScholarshipController;

// ✅ Root Redirect
Route::get('/', function () {
    if (Auth::guard('admin')->check() || (Auth::guard('web')->check() && Auth::guard('web')->user()?->is_admin)) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// ✅ Public Digital Card Routes
Route::get('/card/{slug}.vcf', [\App\Http\Controllers\PublicDigitalCardController::class, 'vcard'])->name('digital-cards.vcf');
Route::get('/card/{slug}/vcard', [\App\Http\Controllers\PublicDigitalCardController::class, 'vcard'])->name('digital-cards.vcard');
Route::get('/card/{slug}', [\App\Http\Controllers\PublicDigitalCardController::class, 'show'])->name('digital-cards.show');
Route::get('/api/card/{slug}', [\App\Http\Controllers\PublicDigitalCardController::class, 'apiShow'])->name('digital-cards.api');

// ✅ Admin Auth Routes
Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ✅ Admin Routes
Route::middleware(['auth:admin,web', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Digital Business Cards Module
    Route::post('/admin/digital-cards/{id}/toggle-status', [\App\Http\Controllers\Admin\DigitalCardController::class, 'toggleStatus'])->name('admin.digital-cards.toggle-status');
    Route::get('/admin/digital-cards/{id}/duplicate', [\App\Http\Controllers\Admin\DigitalCardController::class, 'duplicate'])->name('admin.digital-cards.duplicate');
    Route::resource('/admin/digital-cards', \App\Http\Controllers\Admin\DigitalCardController::class)->names('admin.digital-cards');

    // Mega Menu Management
    Route::resource('/admin/mega-menu', MegaMenuController::class)->names('admin.mega-menu')->middleware('can:department-browse');
    Route::post('/admin/mega-menu/{megaMenu}/status', [MegaMenuController::class, 'updateStatus'])->name('admin.mega-menu.status');

    // Categories
    Route::resource('/admin/categories', CategoryController::class)->middleware('can:department-browse');

    // Career Roadmap
    Route::resource('/admin/career-roadmap-categories', \App\Http\Controllers\Admin\CareerRoadmapCategoryController::class)->names('admin.career-roadmap-categories')->middleware('can:department-browse');
    Route::resource('/admin/career-roadmap-stages', \App\Http\Controllers\Admin\CareerRoadmapStageController::class)->names('admin.career-roadmap-stages')->middleware('can:department-browse');
    Route::resource('/admin/career-roadmap-sub-modules', \App\Http\Controllers\Admin\CareerRoadmapSubModuleController::class)->names('admin.career-roadmap-sub-modules')->middleware('can:department-browse');

    // FAQs
    Route::resource('/admin/faq-categories', \App\Http\Controllers\Admin\FaqCategoryController::class)->names('admin.faq-categories')->middleware('can:department-browse');
    Route::resource('/admin/faq-items', \App\Http\Controllers\Admin\FaqItemController::class)->names('admin.faq-items')->middleware('can:department-browse');

    // Students
    Route::resource('/admin/students', StudentController::class)->middleware('can:department-browse');

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
    Route::resource('/admin/expert-categories', \App\Http\Controllers\Admin\ExpertCategoryController::class)->names('expert-categories')->middleware('can:department-browse');

    // Expert Bookings & Slots
    Route::get('/admin/expert-bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('admin.bookings.index');
    Route::put('/admin/expert-bookings/{id}', [\App\Http\Controllers\Admin\BookingController::class, 'update'])->name('admin.bookings.update');
    Route::get('/admin/slots', [\App\Http\Controllers\Admin\SlotController::class, 'index'])->name('admin.slots.index');
    Route::post('/admin/slots', [\App\Http\Controllers\Admin\SlotController::class, 'store'])->name('admin.slots.store');
    Route::put('/admin/slots/{id}', [\App\Http\Controllers\Admin\SlotController::class, 'update'])->name('admin.slots.update');
    Route::delete('/admin/slots/{id}', [\App\Http\Controllers\Admin\SlotController::class, 'destroy'])->name('admin.slots.destroy');

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

    // Academic Directory Cockpit & Reports
    Route::prefix('/admin/academic-directory')->name('admin.academic-directory.')->group(function () {
        Route::get('/', [AcademicDirectoryController::class, 'index'])->name('index');
        Route::get('/filter-counts', [AcademicDirectoryController::class, 'getFilterCounts'])->name('filter-counts');
        Route::get('/data/organisations', [AcademicDirectoryController::class, 'getOrganisationsData'])->name('data.organisations');
        Route::get('/data/campuses', [AcademicDirectoryController::class, 'getCampusesData'])->name('data.campuses');
        Route::get('/data/departments', [AcademicDirectoryController::class, 'getDepartmentsData'])->name('data.departments');
        Route::get('/data/courses', [AcademicDirectoryController::class, 'getCoursesData'])->name('data.courses');
        Route::get('/cascading-options', [AcademicDirectoryController::class, 'getCascadingOptions'])->name('cascading-options');
        Route::get('/quick-view/{type}/{id}', [AcademicDirectoryController::class, 'getQuickView'])->name('quick-view');
        Route::post('/update-sort-order', [AcademicDirectoryController::class, 'updateSortOrder'])->name('update-sort-order');
        Route::get('/export', [AcademicDirectoryController::class, 'exportDirectory'])->name('export');
    });

    // AI-Powered Organisation Auto-Import
    Route::get('/admin/ai-organisations/create', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'create'])->name('admin.ai-organisations.create');
    Route::get('/admin/ai-organisations/targeted-add', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'targetedAdd'])->name('admin.ai-organisations.targeted-add');
    Route::get('/admin/ai-organisations/cascading-options', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'cascadingOptions'])->name('admin.ai-organisations.cascading-options');
    Route::post('/admin/ai-organisations/preview-prompt', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'previewPrompt'])->name('admin.ai-organisations.preview-prompt');
    Route::post('/admin/ai-organisations/extract', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'extract'])->name('admin.ai-organisations.extract');
    Route::post('/admin/ai-organisations/store', [\App\Http\Controllers\Admin\AiOrganisationImportController::class, 'store'])->name('admin.ai-organisations.store');

    // AI-Powered Organisation Bot Update (Review & Approval Diff)
    Route::get('/admin/ai-organisations/update', [\App\Http\Controllers\Admin\AiOrganisationUpdateController::class, 'index'])->name('admin.ai-organisations.update');
    Route::get('/admin/ai-organisations/{id}/data', [\App\Http\Controllers\Admin\AiOrganisationUpdateController::class, 'getOrganisationData'])->name('admin.ai-organisations.data');
    Route::post('/admin/ai-organisations/fetch-updates', [\App\Http\Controllers\Admin\AiOrganisationUpdateController::class, 'fetchUpdates'])->name('admin.ai-organisations.fetch-updates');
    Route::post('/admin/ai-organisations/apply-updates', [\App\Http\Controllers\Admin\AiOrganisationUpdateController::class, 'applyUpdates'])->name('admin.ai-organisations.apply-updates');

    // Organisation Fields Configuration (Academics Module)
    Route::get('/admin/organisation-fields', [\App\Http\Controllers\Admin\OrganisationFieldConfigController::class, 'index'])->name('admin.organisation-fields.index');
    Route::post('/admin/organisation-fields', [\App\Http\Controllers\Admin\OrganisationFieldConfigController::class, 'store'])->name('admin.organisation-fields.store');
    Route::get('/admin/organisation-fields/audit', [\App\Http\Controllers\Admin\OrganisationFieldConfigController::class, 'audit'])->name('admin.organisation-fields.audit');
    Route::post('/admin/organisation-fields/rescan', [\App\Http\Controllers\Admin\OrganisationFieldConfigController::class, 'rescan'])->name('admin.organisation-fields.rescan');
    Route::post('/admin/organisation-fields/{typeId}/reset', [\App\Http\Controllers\Admin\OrganisationFieldConfigController::class, 'reset'])->name('admin.organisation-fields.reset');

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
    Route::delete('/admin/organisations/{organisation}/documents/{document}', [OrganisationController::class, 'destroyDocument'])->name('admin.organisations.documents.destroy');
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
    Route::delete('/admin/organisations/{organisation}/campuses/{campus}/documents/{document}', [\App\Http\Controllers\Admin\CampusController::class, 'destroyDocument'])->name('admin.organisations.campuses.documents.destroy');

    // Departments (Nested under Campuses contextually, but flat resource for now)
    Route::post('/admin/departments/bulk-destroy', [DepartmentController::class, 'bulkDestroy'])->name('admin.departments.bulk-destroy')->middleware('can:department-browse');
    Route::resource('/admin/departments', DepartmentController::class)->names('admin.departments')->middleware('can:department-browse');
    Route::delete('/admin/departments/{department}/documents/{document}', [DepartmentController::class, 'destroyDocument'])->name('admin.departments.documents.destroy');
    Route::post('/admin/departments/store-draft', [DepartmentController::class, 'storeDraft'])->name('admin.departments.store-draft');
    Route::post('/admin/departments/{department}/autosave-tab', [DepartmentController::class, 'autosaveTab'])->name('admin.departments.autosave-tab');

    // Faculty Module (Academics)
    Route::post('/admin/faculties/bulk-destroy', [\App\Http\Controllers\Admin\FacultyController::class, 'bulkDestroy'])->name('admin.faculties.bulk-destroy');
    Route::patch('/admin/faculties/{faculty}/toggle-status', [\App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('admin.faculties.toggle-status');
    Route::post('/admin/faculties/{faculty}/verification', [\App\Http\Controllers\Admin\FacultyController::class, 'updateVerification'])->name('admin.faculties.verification');
    Route::get('/admin/faculties/ajax/campuses/{organisationId}', [\App\Http\Controllers\Admin\FacultyController::class, 'getCampusesByOrganisation'])->name('admin.faculties.ajax.campuses');
    Route::get('/admin/faculties/ajax/departments', [\App\Http\Controllers\Admin\FacultyController::class, 'getDepartmentsByCampus'])->name('admin.faculties.ajax.departments');
    Route::get('/admin/faculties/ajax/courses', [\App\Http\Controllers\Admin\FacultyController::class, 'getCoursesByOrganisationOrDepartment'])->name('admin.faculties.ajax.courses');
    Route::resource('/admin/faculties', \App\Http\Controllers\Admin\FacultyController::class)->names('admin.faculties');

    // Faculty Masters (Academics)
    Route::patch('/admin/faculty-designations/{facultyDesignation}/toggle-status', [\App\Http\Controllers\Admin\FacultyDesignationController::class, 'toggleStatus'])->name('admin.faculty-designations.toggle-status');
    Route::resource('/admin/faculty-designations', \App\Http\Controllers\Admin\FacultyDesignationController::class)->names('admin.faculty-designations');

    Route::patch('/admin/faculty-types/{facultyType}/toggle-status', [\App\Http\Controllers\Admin\FacultyTypeController::class, 'toggleStatus'])->name('admin.faculty-types.toggle-status');
    Route::resource('/admin/faculty-types', \App\Http\Controllers\Admin\FacultyTypeController::class)->names('admin.faculty-types');


    // Exams
    Route::resource('/admin/exams', \App\Http\Controllers\Admin\ExamController::class)->names('admin.exams')->middleware('can:department-browse');

    // Dynamic Exams
    Route::post('/admin/dynamic-exams/update-sort-order', [\App\Http\Controllers\Admin\DynamicExamController::class, 'updateSortOrder'])->name('admin.dynamic-exams.update-sort-order');
    Route::resource('/admin/dynamic-exams', \App\Http\Controllers\Admin\DynamicExamController::class)->names('admin.dynamic-exams')->middleware('can:department-browse');
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


    // Exam Module (exm_*)
    Route::prefix('admin/exam-module')->name('admin.exam-module.')->group(function () {
        $ns = 'App\Http\Controllers\Admin\ExamModule\\';

        Route::get('attention', [$ns . 'AttentionController', 'index'])->name('attention')->middleware('can:exm-attention-browse');
        Route::get('audit', [$ns . 'AttentionController', 'audit'])->name('audit')->middleware('can:exm-audit-browse');

        Route::resource('conducting-bodies', $ns . 'ConductingBodyController')->except('show')
            ->parameters(['conducting-bodies' => 'conductingBody'])
            ->middlewareFor(['index'], 'can:exm-conducting-bodies-browse')
            ->middlewareFor(['create', 'store'], 'can:exm-conducting-bodies-add')
            ->middlewareFor(['edit', 'update'], 'can:exm-conducting-bodies-edit')
            ->middlewareFor(['destroy'], 'can:exm-conducting-bodies-delete');

        Route::middleware('can:exm-event-types-manage')->group(function () use ($ns) {
            Route::resource('event-types', $ns . 'EventTypeController')->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['event-types' => 'eventType']);
        });

        Route::patch('exams/{exam}/visibility', [$ns . 'ExamController', 'visibility'])->name('exams.visibility')->middleware('can:exm-exams-publish');
        Route::resource('exams', $ns . 'ExamController')
            ->middlewareFor(['index', 'show'], 'can:exm-exams-browse')
            ->middlewareFor(['create', 'store'], 'can:exm-exams-add')
            ->middlewareFor(['edit', 'update'], 'can:exm-exams-edit')
            ->middlewareFor(['destroy'], 'can:exm-exams-delete');

        Route::middleware('can:exm-cycles-manage')->group(function () use ($ns) {
            Route::get('exams/{exam}/cycles/create', [$ns . 'CycleController', 'create'])->name('cycles.create');
            Route::post('exams/{exam}/cycles', [$ns . 'CycleController', 'store'])->name('cycles.store');
            Route::get('cycles/{cycle}', [$ns . 'CycleController', 'show'])->name('cycles.show');
            Route::get('cycles/{cycle}/edit', [$ns . 'CycleController', 'edit'])->name('cycles.edit');
            Route::put('cycles/{cycle}', [$ns . 'CycleController', 'update'])->name('cycles.update');
            Route::delete('cycles/{cycle}', [$ns . 'CycleController', 'destroy'])->name('cycles.destroy');
            Route::post('cycles/{cycle}/copy', [$ns . 'CycleController', 'copy'])->name('cycles.copy');
            Route::patch('cycles/{cycle}/current', [$ns . 'CycleController', 'setCurrent'])->name('cycles.current');

            Route::post('cycles/{cycle}/stages', [$ns . 'CycleController', 'storeStage'])->name('stages.store');
            Route::put('stages/{stage}', [$ns . 'CycleController', 'updateStage'])->name('stages.update');
            Route::delete('stages/{stage}', [$ns . 'CycleController', 'destroyStage'])->name('stages.destroy');

            Route::post('cycles/{cycle}/events', [$ns . 'EventController', 'store'])->name('events.store');
            Route::put('events/{event}', [$ns . 'EventController', 'update'])->name('events.update');
            Route::delete('events/{event}', [$ns . 'EventController', 'destroy'])->name('events.destroy');
        });
        // Cycle content blocks (eligibility, pattern, fees, syllabus, centres, documents, papers, faqs)
        Route::middleware('can:exm-content-manage')->group(function () use ($ns) {
            $blocks = 'eligibility|pattern|fees|syllabus|centres|documents|papers|faqs';
            Route::post('cycles/{cycle}/content/{block}', [$ns . 'ContentController', 'store'])->where('block', $blocks)->name('content.store');
            Route::put('content/{block}/{id}', [$ns . 'ContentController', 'update'])->where('block', $blocks)->whereNumber('id')->name('content.update');
            Route::delete('content/{block}/{id}', [$ns . 'ContentController', 'destroy'])->where('block', $blocks)->whereNumber('id')->name('content.destroy');
            Route::post('cycles/{cycle}/custom-values', [$ns . 'CustomFieldController', 'saveValues'])->name('custom-values.save');
        });

        // Schema Builder and City master
        Route::middleware('can:exm-schema-manage')->group(function () use ($ns) {
            Route::get('exams/{exam}/fields', [$ns . 'CustomFieldController', 'index'])->name('fields.index');
            Route::post('exams/{exam}/fields', [$ns . 'CustomFieldController', 'store'])->name('fields.store');
            Route::put('fields/{field}', [$ns . 'CustomFieldController', 'update'])->name('fields.update');
            Route::delete('fields/{field}', [$ns . 'CustomFieldController', 'destroy'])->name('fields.destroy');
        });
        Route::middleware('can:exm-cities-manage')->group(function () use ($ns) {
            Route::resource('cities', $ns . 'CityController')->only(['index', 'store', 'update', 'destroy']);
        });

        Route::patch('cycles/{cycle}/public',[$ns . 'CycleController', 'togglePublic'])->name('cycles.public')->middleware('can:exm-exams-publish');
        Route::post('events/{event}/verify', [$ns . 'EventController', 'verify'])->name('events.verify')->middleware('can:exm-exams-verify');
    });

    // Counselling Module (cns_*)
    Route::prefix('admin/counselling')->name('admin.counselling.')->group(function () {
        $ns = 'App\Http\Controllers\Admin\Counselling\\';
        $masters = 'categories|quotas|round-types|candidate-actions|document-types';

        Route::redirect('/', '/admin/counselling/counsellings');

        Route::middleware('can:cns-masters-manage')->group(function () use ($ns, $masters) {
            Route::get('masters/{master}', [$ns . 'MasterController', 'index'])->where('master', $masters)->name('masters.index');
            Route::post('masters/{master}', [$ns . 'MasterController', 'store'])->where('master', $masters)->name('masters.store');
            Route::put('masters/{master}/{id}', [$ns . 'MasterController', 'update'])->where('master', $masters)->whereNumber('id')->name('masters.update');
            Route::delete('masters/{master}/{id}', [$ns . 'MasterController', 'destroy'])->where('master', $masters)->whereNumber('id')->name('masters.destroy');
        });
        Route::middleware('can:cns-codes-manage')->group(function () use ($ns) {
            Route::get('codes', [$ns . 'InstituteCodeController', 'index'])->name('codes.index');
            Route::post('codes', [$ns . 'InstituteCodeController', 'store'])->name('codes.store');
            Route::put('codes/{code}', [$ns . 'InstituteCodeController', 'update'])->name('codes.update');
            Route::delete('codes/{code}', [$ns . 'InstituteCodeController', 'destroy'])->name('codes.destroy');
        });

        // Exam Module hook: counsellings mapped to one exam
        Route::get('exams/{exam}', [$ns . 'CounsellingController', 'forExam'])->name('exams.counselling')->middleware('can:cns-counsellings-browse');
        Route::post('exams/{exam}/map', [$ns . 'CounsellingController', 'mapExistingFromExam'])->name('exams.map')->middleware('can:cns-counsellings-edit');

        Route::patch('counsellings/{counselling}/visibility', [$ns . 'CounsellingController', 'visibility'])->name('counsellings.visibility')->middleware('can:cns-counsellings-publish');
        Route::resource('counsellings', $ns . 'CounsellingController')->parameters(['counsellings' => 'counselling'])
            ->middlewareFor(['index', 'show'], 'can:cns-counsellings-browse')
            ->middlewareFor(['create', 'store'], 'can:cns-counsellings-add')
            ->middlewareFor(['edit', 'update'], 'can:cns-counsellings-edit')
            ->middlewareFor(['destroy'], 'can:cns-counsellings-delete');

        Route::middleware('can:cns-counsellings-edit')->group(function () use ($ns) {
            Route::post('counsellings/{counselling}/exams', [$ns . 'CounsellingController', 'mapExam'])->name('mappings.store');
            Route::put('mappings/{mapping}', [$ns . 'CounsellingController', 'updateMapping'])->name('mappings.update');
            Route::delete('mappings/{mapping}', [$ns . 'CounsellingController', 'unmapExam'])->name('mappings.destroy');

            Route::get('counsellings/{counselling}/cycles/create', [$ns . 'CycleController', 'create'])->name('cycles.create');
            Route::post('counsellings/{counselling}/cycles', [$ns . 'CycleController', 'store'])->name('cycles.store');
            Route::get('cycles/{cycle}/edit', [$ns . 'CycleController', 'edit'])->name('cycles.edit');
            Route::put('cycles/{cycle}', [$ns . 'CycleController', 'update'])->name('cycles.update');
            Route::delete('cycles/{cycle}', [$ns . 'CycleController', 'destroy'])->name('cycles.destroy');
            Route::patch('cycles/{cycle}/current', [$ns . 'CycleController', 'setCurrent'])->name('cycles.current');

            Route::post('cycles/{cycle}/copy', [$ns . 'CycleController', 'copy'])->name('cycles.copy');

            // Eligibility, process, fees, documents, rules: at cycle level, or as a round override / addition
            $blocks = 'eligibility|process|fees|documents|rules';
            Route::post('cycles/{cycle}/blocks/{block}', [$ns . 'BlockController', 'storeForCycle'])->where('block', $blocks)->name('blocks.store-cycle');
            Route::post('rounds/{round}/blocks/{block}', [$ns . 'BlockController', 'storeForRound'])->where('block', $blocks)->name('blocks.store-round');
            Route::post('rounds/{round}/blocks/{block}/{id}/override', [$ns . 'BlockController', 'override'])->where('block', $blocks)->whereNumber('id')->name('blocks.override');
            Route::put('blocks/{block}/{id}', [$ns . 'BlockController', 'update'])->where('block', $blocks)->whereNumber('id')->name('blocks.update');
            Route::delete('blocks/{block}/{id}', [$ns . 'BlockController', 'destroy'])->where('block', $blocks)->whereNumber('id')->name('blocks.destroy');

            Route::post('cycles/{cycle}/rounds', [$ns . 'CycleController', 'storeRound'])->name('rounds.store');
            Route::put('rounds/{round}', [$ns . 'CycleController', 'updateRound'])->name('rounds.update');
            Route::delete('rounds/{round}', [$ns . 'CycleController', 'destroyRound'])->name('rounds.destroy');

            Route::post('cycles/{cycle}/events', [$ns . 'EventController', 'storeForCycle'])->name('events.store-cycle');
            Route::post('rounds/{round}/events', [$ns . 'EventController', 'storeForRound'])->name('events.store-round');
            Route::put('events/{event}', [$ns . 'EventController', 'update'])->name('events.update');
            Route::delete('events/{event}', [$ns . 'EventController', 'destroy'])->name('events.destroy');
        });
        Route::middleware('can:cns-counsellings-browse')->group(function () use ($ns) {
            Route::get('cycles/{cycle}', [$ns . 'CycleController', 'show'])->name('cycles.show');
            Route::get('rounds/{round}', [$ns . 'CycleController', 'showRound'])->name('rounds.show');
        });
        // Phase B: participants, seat matrix, results, cutoffs
        Route::middleware('can:cns-counsellings-browse')->group(function () use ($ns) {
            Route::get('cycles/{cycle}/participants', [$ns . 'ParticipantController', 'index'])->name('participants.index');
            Route::get('rounds/{round}/seats', [$ns . 'SeatMatrixController', 'index'])->name('seats.index');
            Route::get('seat-versions/{version}', [$ns . 'SeatMatrixController', 'show'])->name('seats.show');
            Route::get('seat-versions/{version}/diff', [$ns . 'SeatMatrixController', 'diff'])->name('seats.diff');
            Route::get('rounds/{round}/cutoffs', [$ns . 'CutoffController', 'index'])->name('cutoffs.index');
            Route::get('imports/lookup', [$ns . 'ImportController', 'lookup'])->name('imports.lookup');
        });
        Route::middleware('can:cns-counsellings-edit')->group(function () use ($ns) {
            Route::post('cycles/{cycle}/participants', [$ns . 'ParticipantController', 'store'])->name('participants.store');
            Route::put('participants/{participant}', [$ns . 'ParticipantController', 'update'])->name('participants.update');
            Route::delete('participants/{participant}', [$ns . 'ParticipantController', 'destroy'])->name('participants.destroy');
            Route::patch('seat-versions/{version}/live', [$ns . 'SeatMatrixController', 'makeLive'])->name('seats.live');
            Route::delete('seat-versions/{version}', [$ns . 'SeatMatrixController', 'destroy'])->name('seats.destroy');
            Route::post('rounds/{round}/results', [$ns . 'ResultController', 'store'])->name('results.store');
            Route::put('results/{result}', [$ns . 'ResultController', 'update'])->name('results.update');
            Route::delete('results/{result}', [$ns . 'ResultController', 'destroy'])->name('results.destroy');
            Route::post('rounds/{round}/cutoffs', [$ns . 'CutoffController', 'store'])->name('cutoffs.store');
            Route::put('cutoffs/{cutoff}', [$ns . 'CutoffController', 'update'])->name('cutoffs.update');
            Route::delete('cutoffs/{cutoff}', [$ns . 'CutoffController', 'destroy'])->name('cutoffs.destroy');
        });
        Route::post('rounds/{round}/cutoffs/clear', [$ns . 'CutoffController', 'clear'])->name('cutoffs.clear')->middleware('can:cns-counsellings-delete');

        // Follow / alert monitor
        Route::get('alerts', [$ns . 'AlertAdminController', 'index'])->name('alerts.index')->middleware('can:cns-counsellings-browse');
        Route::post('alerts/run', [$ns . 'AlertAdminController', 'run'])->name('alerts.run')->middleware('can:cns-counsellings-edit');
        Route::post('alerts/retry', [$ns . 'AlertAdminController', 'retry'])->name('alerts.retry')->middleware('can:cns-counsellings-edit');

        // CSV import centre
        Route::middleware('can:cns-import-manage')->prefix('imports')->name('imports.')->group(function () use ($ns) {
            Route::get('/', [$ns . 'ImportController', 'index'])->name('index');
            Route::get('create', [$ns . 'ImportController', 'create'])->name('create');
            Route::post('/', [$ns . 'ImportController', 'store'])->name('store');
            Route::get('template/{type}', [$ns . 'ImportController', 'template'])->where('type', 'participants|seats|cutoffs')->name('template');
            Route::get('{batch}', [$ns . 'ImportController', 'show'])->whereNumber('batch')->name('show');
            Route::post('{batch}/fix', [$ns . 'ImportController', 'fix'])->whereNumber('batch')->name('fix');
            Route::post('{batch}/accept', [$ns . 'ImportController', 'accept'])->whereNumber('batch')->name('accept');
            Route::post('{batch}/confirm', [$ns . 'ImportController', 'confirm'])->whereNumber('batch')->name('confirm');
            Route::post('{batch}/cancel', [$ns . 'ImportController', 'cancel'])->whereNumber('batch')->name('cancel');
            Route::get('{batch}/errors', [$ns . 'ImportController', 'errors'])->whereNumber('batch')->name('errors');
            Route::delete('aliases/{alias}', [$ns . 'ImportController', 'destroyAlias'])->name('aliases.destroy');
        });
        Route::post('imports/{batch}/undo', [$ns . 'ImportController', 'undo'])->whereNumber('batch')->name('imports.undo')->middleware('can:cns-import-undo');

        Route::patch('cycles/{cycle}/public', [$ns . 'CycleController', 'togglePublic'])->name('cycles.public')->middleware('can:cns-counsellings-publish');
        Route::post('cycles/{cycle}/verify', [$ns . 'CycleController', 'verify'])->name('cycles.verify')->middleware('can:cns-counsellings-verify');
        Route::post('events/{event}/verify', [$ns . 'EventController', 'verify'])->name('events.verify')->middleware('can:cns-counsellings-verify');
    });

    // Database backup: open /admin/database-backup while logged in as an admin and the .sql.gz downloads (not linked from any menu).
    // At most 5 downloads an hour, every download audit-logged.
    Route::get('/admin/database-backup', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'download'])->name('admin.database-backup.download')->middleware('throttle:5,60');

    // Rankings & Accreditation (entity identity, rankings, accreditations, verification)
    Route::prefix('admin/quality')->name('admin.quality.')->group(function () {
        $ns = 'App\Http\Controllers\Admin\Quality\\';
        $masters = 'ranking-bodies|ranking-categories|accreditation-bodies';

        Route::middleware('can:quality-browse')->group(function () use ($ns) {
            Route::get('/', [$ns . 'DashboardController', 'index'])->name('dashboard');
            Route::get('review', [$ns . 'RecordController', 'review'])->name('review');
            Route::get('approvals/coverage', [$ns . 'ApprovalCoverageController', 'index'])->name('approvals.coverage');
            Route::get('entity/{type}/{id}', [$ns . 'RecordController', 'entity'])->name('entity');
            Route::get('evidence/{kind}/{id}', [$ns . 'RecordController', 'evidence'])->where('kind', 'ranking|accreditation|approval|placement|review|award')->whereNumber('id')->name('evidence');
        });
        Route::middleware('can:quality-edit')->group(function () use ($ns) {
            Route::post('entity/{type}/{id}/rankings', [$ns . 'RecordController', 'storeRanking'])->name('rankings.store');
            Route::put('rankings/{ranking}', [$ns . 'RecordController', 'updateRanking'])->name('rankings.update');
            Route::post('entity/{type}/{id}/accreditations', [$ns . 'RecordController', 'storeAccreditation'])->name('accreditations.store');
            Route::post('entity/{type}/{id}/approvals', [$ns . 'RecordController', 'storeApproval'])->name('approvals.store');
            Route::post('entity/{type}/{id}/placements', [$ns . 'RecordController', 'storePlacement'])->name('placements.store');
            Route::post('entity/{type}/{id}/awards', [$ns . 'RecordController', 'storeAward'])->name('awards.store');
            Route::put('awards/{award}', [$ns . 'RecordController', 'updateAward'])->name('awards.update');
            Route::post('review/bulk', [$ns . 'RecordController', 'bulkAction'])->name('review.bulk');
            Route::put('placements/{placement}', [$ns . 'RecordController', 'updatePlacement'])->name('placements.update');
            Route::post('entity/{type}/{id}/reviews', [$ns . 'RecordController', 'storeReview'])->name('reviews.store');
            Route::put('reviews/{review}', [$ns . 'RecordController', 'updateReview'])->name('reviews.update');
            Route::put('approvals/{approval}', [$ns . 'RecordController', 'updateApproval'])->name('approvals.update');
            Route::put('accreditations/{accreditation}', [$ns . 'RecordController', 'updateAccreditation'])->name('accreditations.update');
        });
        Route::middleware('can:quality-delete')->group(function () use ($ns) {
            Route::delete('rankings/{ranking}', [$ns . 'RecordController', 'destroyRanking'])->name('rankings.destroy');
            Route::delete('accreditations/{accreditation}', [$ns . 'RecordController', 'destroyAccreditation'])->name('accreditations.destroy');
            Route::delete('approvals/{approval}', [$ns . 'RecordController', 'destroyApproval'])->name('approvals.destroy');
            Route::delete('placements/{placement}', [$ns . 'RecordController', 'destroyPlacement'])->name('placements.destroy');
            Route::delete('awards/{award}', [$ns . 'RecordController', 'destroyAward'])->name('awards.destroy');
            Route::delete('reviews/{review}', [$ns . 'RecordController', 'destroyReview'])->name('reviews.destroy');
        });
        Route::middleware('can:quality-verify')->group(function () use ($ns) {
            Route::post('records/{kind}/{id}/verify', [$ns . 'RecordController', 'verify'])->where('kind', 'ranking|accreditation|approval|placement|review|award')->whereNumber('id')->name('records.verify');
            Route::post('records/{kind}/{id}/reject', [$ns . 'RecordController', 'reject'])->where('kind', 'ranking|accreditation|approval|placement|review|award')->whereNumber('id')->name('records.reject');
            Route::post('review/verify', [$ns . 'RecordController', 'bulkVerify'])->name('review.verify');
        });
        Route::middleware('can:quality-import')->group(function () use ($ns) {
            Route::get('reviews/import', [$ns . 'ReviewImportController', 'index'])->name('reviews.import');
            Route::get('reviews/import/template', [$ns . 'ReviewImportController', 'template'])->name('reviews.import.template');
            Route::get('reviews/import/ids', [$ns . 'ReviewImportController', 'ids'])->name('reviews.import.ids');
            Route::get('reviews/import/organisations', [$ns . 'ReviewImportController', 'organisations'])->name('reviews.import.organisations');
            Route::post('reviews/import/preview', [$ns . 'ReviewImportController', 'preview'])->name('reviews.import.preview');
            Route::post('reviews/import/confirm', [$ns . 'ReviewImportController', 'confirm'])->name('reviews.import.confirm');
            Route::post('reviews/import/undo', [$ns . 'ReviewImportController', 'undo'])->name('reviews.import.undo');
            Route::get('placements/import', [$ns . 'PlacementImportController', 'index'])->name('placements.import');
            Route::get('placements/import/template', [$ns . 'PlacementImportController', 'template'])->name('placements.import.template');
            Route::get('placements/import/organisations', [$ns . 'PlacementImportController', 'organisations'])->name('placements.import.organisations');
            Route::post('placements/import/preview', [$ns . 'PlacementImportController', 'preview'])->name('placements.import.preview');
            Route::post('placements/import/confirm', [$ns . 'PlacementImportController', 'confirm'])->name('placements.import.confirm');
            Route::post('placements/import/undo', [$ns . 'PlacementImportController', 'undo'])->name('placements.import.undo');
            Route::get('approvals/import', [$ns . 'ApprovalImportController', 'index'])->name('approvals.import');
            Route::get('approvals/import/template', [$ns . 'ApprovalImportController', 'template'])->name('approvals.import.template');
            Route::post('approvals/import/preview', [$ns . 'ApprovalImportController', 'preview'])->name('approvals.import.preview');
            Route::post('approvals/import/confirm', [$ns . 'ApprovalImportController', 'confirm'])->name('approvals.import.confirm');
            Route::post('approvals/import/undo', [$ns . 'ApprovalImportController', 'undo'])->name('approvals.import.undo');
        });
        Route::middleware('can:quality-masters-manage')->group(function () use ($ns, $masters) {
            // One-page data update, run after the SQL file has created the tables (safe to run again).
            Route::get('setup', [$ns . 'SetupController', 'index'])->name('setup');
            Route::post('setup', [$ns . 'SetupController', 'run'])->name('setup.run');
            Route::get('masters/{master}', [$ns . 'MasterController', 'index'])->where('master', $masters)->name('masters.index');
            Route::post('masters/{master}', [$ns . 'MasterController', 'store'])->where('master', $masters)->name('masters.store');
            Route::put('masters/{master}/{id}', [$ns . 'MasterController', 'update'])->where('master', $masters)->whereNumber('id')->name('masters.update');
            Route::delete('masters/{master}/{id}', [$ns . 'MasterController', 'destroy'])->where('master', $masters)->whereNumber('id')->name('masters.destroy');
        });
        Route::middleware('can:quality-identity-manage')->group(function () use ($ns) {
            Route::get('identity', [$ns . 'IdentityController', 'index'])->name('identity.index');
            Route::put('identity/{level}/{id}', [$ns . 'IdentityController', 'update'])->name('identity.update');
            Route::get('identity-template', [$ns . 'IdentityController', 'template'])->name('identity.template');
            Route::post('identity/import', [$ns . 'IdentityController', 'import'])->name('identity.import');
            Route::post('identity/import/confirm', [$ns . 'IdentityController', 'confirmImport'])->name('identity.import.confirm');
            Route::get('groups', [$ns . 'GroupController', 'index'])->name('groups.index');
            Route::post('groups', [$ns . 'GroupController', 'store'])->name('groups.store');
            Route::put('groups/{group}', [$ns . 'GroupController', 'update'])->name('groups.update');
            Route::delete('groups/{group}', [$ns . 'GroupController', 'destroy'])->name('groups.destroy');
            Route::get('moves', [$ns . 'MoveController', 'index'])->name('moves.index');
            Route::get('moves/search', [$ns . 'MoveController', 'search'])->name('moves.search');
            Route::post('moves/preview', [$ns . 'MoveController', 'preview'])->name('moves.preview');
            Route::post('moves/apply', [$ns . 'MoveController', 'apply'])->name('moves.apply');
            Route::post('moves/{move}/undo', [$ns . 'MoveController', 'undo'])->whereNumber('move')->name('moves.undo');
        });
    });

    // Exam Stage Data Management
    Route::get('/admin/exams/{exam}/stages/{stage}/edit', [ExamStageDataController::class, 'edit'])->name('admin.exams.stages.edit');
    Route::post('/admin/exams/{exam}/interview/update', [ExamStageDataController::class, 'updateInterview'])->name('admin.exams.interview.update');
    Route::post('/admin/exams/{exam}/skill/update', [ExamStageDataController::class, 'updateSkill'])->name('admin.exams.skill.update');
    Route::post('/admin/exams/{exam}/medical/update', [ExamStageDataController::class, 'updateMedical'])->name('admin.exams.medical.update');
    Route::post('/admin/exams/{exam}/preliminary/update', [ExamStageDataController::class, 'updatePreliminary'])->name('admin.exams.preliminary.update');
    Route::post('/admin/exams/{exam}/main/update', [ExamStageDataController::class, 'updateMain'])->name('admin.exams.main.update');

    // Exam Subjects
    Route::resource('/admin/exam-subjects', \App\Http\Controllers\Admin\ExamSubjectController::class)->names('admin.exam-subjects')->middleware('can:department-browse');

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
    Route::get('/admin/leads', [LeadController::class, 'index'])->name('leads.index')->middleware('can:lead-browse');
    Route::patch('/admin/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
    Route::delete('/admin/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index')->middleware('can:settings-browse');
    Route::post('/admin/settings/update', [SettingController::class, 'update'])->name('admin.settings.update');

    // SEO Organization Settings
    Route::get('/admin/seo-organization', [\App\Http\Controllers\Admin\SeoOrganizationSettingController::class, 'edit'])->name('admin.seo_organization.edit')->middleware('can:seo-organization-browse');
    Route::post('/admin/seo-organization', [\App\Http\Controllers\Admin\SeoOrganizationSettingController::class, 'update'])->name('admin.seo_organization.update');

    // Homepage SEO
    Route::get('/admin/seo-homepage', [\App\Http\Controllers\Admin\SeoHomepageController::class, 'edit'])->name('admin.seo_homepage.edit')->middleware('can:seo-homepage-browse');
    Route::post('/admin/seo-homepage', [\App\Http\Controllers\Admin\SeoHomepageController::class, 'update'])->name('admin.seo_homepage.update');

    // Global SEO Defaults
    Route::get('/admin/seo-defaults', [\App\Http\Controllers\Admin\SeoDefaultController::class, 'edit'])->name('admin.seo_defaults.edit')->middleware('can:seo-defaults-browse');
    Route::post('/admin/seo-defaults', [\App\Http\Controllers\Admin\SeoDefaultController::class, 'update'])->name('admin.seo_defaults.update');

    // About Us Page
    Route::get('/admin/about-us', [AboutUsController::class, 'edit'])->name('admin.about_us.edit')->middleware('can:about-us-browse');
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

    Route::post('/admin/about-us/teams', [AboutUsController::class, 'storeTeam'])->name('admin.about_us.teams.store');
    Route::post('/admin/about-us/teams/{id}', [AboutUsController::class, 'updateTeam'])->name('admin.about_us.teams.update');
    Route::delete('/admin/about-us/teams/{id}', [AboutUsController::class, 'destroyTeam'])->name('admin.about_us.teams.destroy');

    Route::post('/admin/about-us/advisory-boards', [AboutUsController::class, 'storeAdvisoryBoard'])->name('admin.about_us.advisory_boards.store');
    Route::post('/admin/about-us/advisory-boards/{id}', [AboutUsController::class, 'updateAdvisoryBoard'])->name('admin.about_us.advisory_boards.update');
    Route::delete('/admin/about-us/advisory-boards/{id}', [AboutUsController::class, 'destroyAdvisoryBoard'])->name('admin.about_us.advisory_boards.destroy');

    // Contact Us Setting
    Route::get('/admin/contact-us', [ContactUsDetailController::class, 'edit'])->name('admin.contact-us.edit')->middleware('can:contact-us-browse');
    Route::put('/admin/contact-us', [ContactUsDetailController::class, 'update'])->name('admin.contact-us.update');

    // Commission Settings
    Route::get('/admin/commission', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('admin.commission.index')->middleware('can:commission-browse');
    Route::post('/admin/commission/global', [\App\Http\Controllers\Admin\CommissionController::class, 'updateGlobal'])->name('admin.commission.global.update');
    Route::post('/admin/commission/category', [\App\Http\Controllers\Admin\CommissionController::class, 'updateCategory'])->name('admin.commission.category.update');

    // Hero Sliders
    Route::resource('/admin/hero-sliders', HeroSliderController::class)->names('admin.hero-sliders')->middleware('can:department-browse');
    Route::post('/admin/hero-sliders/{hero_slider}/toggle-status', [HeroSliderController::class, 'updateStatus'])->name('admin.hero-sliders.toggle-status');
    Route::post('/admin/hero-sliders/global-banner-toggle', [HeroSliderController::class, 'toggleGlobalBanner'])->name('admin.hero-sliders.global-banner-toggle');

    // Specialized Courses (Home Services)
    Route::resource('/admin/home-services', \App\Http\Controllers\Admin\HomeServiceController::class)->names('admin.home-services')->middleware('can:department-browse');

    // Benefits (Why Choose Us)
    Route::resource('/admin/home-benefits', \App\Http\Controllers\Admin\HomeBenefitController::class)->names('admin.home-benefits')->middleware('can:department-browse');

    // Filtered Pages
    Route::resource('/admin/filtered-pages', \App\Http\Controllers\Admin\FilteredPageController::class)->names('admin.filtered-pages');

    // Advanced Scholarships
    Route::resource('/admin/scholarships', \App\Http\Controllers\Admin\ScholarshipController::class)->names('admin.scholarships')->middleware('can:department-browse');
    // Autosave routes for create and edit
    Route::post('/admin/scholarships/autosave', [\App\Http\Controllers\Admin\ScholarshipController::class, 'autosave'])->name('admin.scholarships.autosave');
    Route::post('/admin/scholarships/{scholarship}/autosave', [\App\Http\Controllers\Admin\ScholarshipController::class, 'autosaveUpdate'])->name('admin.scholarships.autosaveUpdate');
    // Trash and restore routes for soft-deleted scholarships
    Route::get('/admin/scholarships/trash', [\App\Http\Controllers\Admin\ScholarshipController::class, 'trash'])->name('admin.scholarships.trash');
    Route::post('/admin/scholarships/{id}/restore', [\App\Http\Controllers\Admin\ScholarshipController::class, 'restore'])->name('admin.scholarships.restore');
    // Trending Skills
    Route::resource('/admin/trending-skills', \App\Http\Controllers\Admin\TrendingSkillController::class)->names('admin.trending-skills')->middleware('can:department-browse');

    // Trending Courses
    Route::resource('/admin/trending-courses', \App\Http\Controllers\Admin\TrendingCourseController::class)->names('admin.trending-courses')->middleware('can:department-browse');

    // Homepage Stream Tabs (Leading Universities Stream Management)
    Route::resource('/admin/homepage-stream-tabs', \App\Http\Controllers\Admin\HomepageStreamTabController::class)->names('admin.homepage-stream-tabs')->middleware('can:department-browse');

    // Dynamic Pages
    Route::resource('/admin/pages', \App\Http\Controllers\Admin\PageController::class)->names('admin.pages')->middleware('can:department-browse');

    // Company Marquee
    Route::post('/admin/school-marquees/update-direction', [\App\Http\Controllers\Admin\SchoolMarqueeController::class, 'updateDirection'])->name('admin.school-marquees.update-direction');
    Route::resource('/admin/school-marquees', \App\Http\Controllers\Admin\SchoolMarqueeController::class)->names('admin.school-marquees')->middleware('can:department-browse');
    Route::post('/admin/school-marquees/{school_marquee}/toggle-status', [\App\Http\Controllers\Admin\SchoolMarqueeController::class, 'toggleStatus'])->name('admin.school-marquees.toggle-status');

    // Institute Marquee
    Route::post('/admin/institute-marquees/update-direction', [\App\Http\Controllers\Admin\InstituteMarqueeController::class, 'updateDirection'])->name('admin.institute-marquees.update-direction');
    Route::resource('/admin/institute-marquees', \App\Http\Controllers\Admin\InstituteMarqueeController::class)->names('admin.institute-marquees')->middleware('can:department-browse');
    Route::post('/admin/institute-marquees/{institute_marquee}/toggle-status', [\App\Http\Controllers\Admin\InstituteMarqueeController::class, 'toggleStatus'])->name('admin.institute-marquees.toggle-status');

    // Video Testimonials
    Route::resource('/admin/video-testimonials', VideoTestimonialController::class)->names('admin.video-testimonials')->middleware('can:department-browse');

    // Noteworthy Categories
    Route::resource('/admin/noteworthy-categories', NoteworthyCategoryController::class)->names('admin.noteworthy-categories')->middleware('can:department-browse');

    // Noteworthy Mentions
    Route::resource('/admin/noteworthy-mentions', NoteworthyMentionController::class)->names('admin.noteworthy-mentions')->middleware('can:department-browse');

    // Organisation Courses
    Route::post('/admin/organisation-courses/bulk-destroy', [OrganisationCourseController::class, 'bulkDestroy'])->name('admin.organisation-courses.bulk-destroy');
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
    Route::delete('/admin/organisation-courses/{organisationCourse}/documents/{document}', [OrganisationCourseController::class, 'destroyDocument'])->name('admin.organisation-courses.documents.destroy');

    // Master Course Management
    Route::resource('/admin/courses', CourseController::class)->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);
    Route::delete('/admin/courses/{course}/documents/{document}', [CourseController::class, 'destroyDocument'])->name('admin.courses.documents.destroy');
    Route::post('/admin/courses/fetch-bot-data', [CourseController::class, 'fetchBotData'])
        ->name('admin.courses.fetch-bot-data');
    Route::get('admin/courses/{course}/duplicate', [CourseController::class, 'duplicate'])
        ->name('admin.courses.duplicate');

    // Community Management
    Route::resource('/admin/community-categories', CommunityCategoryController::class)->names('admin.community-categories')->middleware('can:department-browse');
    Route::resource('/admin/community-questions', CommunityQuestionController::class)->names('admin.community-questions')->middleware('can:department-browse');
    Route::patch('/admin/community-questions/{question}/toggle-verify', [CommunityQuestionController::class, 'toggleVerify'])->name('admin.community-questions.toggle-verify');

    Route::resource('/admin/community-replies', \App\Http\Controllers\Admin\CommunityReplyController::class)->names('admin.community-replies')->middleware('can:department-browse');
    Route::patch('/admin/community-replies/{reply}/toggle-active', [\App\Http\Controllers\Admin\CommunityReplyController::class, 'toggleActive'])->name('admin.community-replies.toggle-active');

    // Header Links
    Route::patch('header-links/{headerLink}/toggle-status', [\App\Http\Controllers\Admin\HeaderLinkController::class, 'toggleStatus'])->name('admin.header-links.toggle-status');
    Route::post('header-links/{headerLink}/toggle-status', [\App\Http\Controllers\Admin\HeaderLinkController::class, 'toggleStatus']);
    Route::resource('header-links', \App\Http\Controllers\Admin\HeaderLinkController::class)->names('admin.header-links')->middleware('can:header-links-browse');

    // Main Header Menus
    Route::resource('header-menus', \App\Http\Controllers\Admin\HeaderMenuController::class)->names('admin.header-menus')->middleware('can:header-menus-browse');

    // Footer Setup
    Route::get('footer-setup', [\App\Http\Controllers\Admin\FooterSetupController::class, 'index'])->name('admin.footer-setup.index')->middleware('can:footer-setup-browse');
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
    Route::get('homepage-sections', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'index'])->name('homepage-sections.index')->middleware('can:homepage-sections-browse');
    Route::get('admin/homepage-sections', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'index']);
    Route::get('homepage-sections/{homepageSection}/edit', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'edit'])->name('homepage-sections.edit');
    Route::get('admin/homepage-sections/{homepageSection}/edit', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'edit']);
    Route::put('homepage-sections/{homepageSection}/details', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'updateDetails'])->name('homepage-sections.update-details');
    Route::patch('homepage-sections/{homepageSection}', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'update'])->name('homepage-sections.update');
    Route::post('homepage-sections/order', [\App\Http\Controllers\Admin\HomepageSectionController::class, 'updateOrder'])->name('homepage-sections.update-order');

    // Master Management
    Route::prefix('masters')->group(function () {
        Route::resource('facilities', \App\Http\Controllers\Admin\FacilityController::class)->names('admin.facilities')->except(['create', 'show', 'edit'])->middleware('can:facilities-browse');
        Route::resource('sports', \App\Http\Controllers\Admin\SportController::class); // Master sport route
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('program-levels', \App\Http\Controllers\Admin\ProgramLevelController::class)->middleware('can:program-levels-browse');
        Route::resource('program-types', \App\Http\Controllers\Admin\ProgramTypeController::class)->middleware('can:program-types-browse');
        Route::resource('stream-offereds', \App\Http\Controllers\Admin\StreamOfferedController::class)->middleware('can:stream-offereds-browse');
        Route::resource('disciplines', \App\Http\Controllers\Admin\DisciplineController::class)->middleware('can:disciplines-browse');
        Route::resource('specializations', \App\Http\Controllers\Admin\SpecializationController::class)->middleware('can:specializations-browse');
        Route::resource('organisation-types', \App\Http\Controllers\Admin\OrganisationTypeController::class)->middleware('can:organisation-types-browse');
        // Retired: the old Accreditation & Approval master now lives in Rankings & Accreditation > Accreditation & approval bodies.
        Route::get('accreditation-approvals/{any?}', fn () => redirect()->route('admin.quality.masters.index', 'accreditation-bodies'))->where('any', '.*')->name('accreditation-approvals.index');
        Route::resource('campus-types', \App\Http\Controllers\Admin\CampusTypeController::class)->middleware('can:campus-types-browse');
        Route::resource('campus_type_new', \App\Http\Controllers\Admin\CampusTypeNewController::class)->middleware('can:campus-types-browse');
        Route::resource('academic-classes', \App\Http\Controllers\Admin\AcademicClassController::class);
        Route::resource('coaching-categories', \App\Http\Controllers\Admin\CoachingCategoryController::class)->middleware('can:coaching-categories-browse');
        Route::resource('course-types', \App\Http\Controllers\Admin\CourseTypeController::class)->middleware('can:course-types-browse');
        Route::resource('exam-categories', \App\Http\Controllers\Admin\ExamCategoryController::class)->middleware('can:exam-categories-browse');
        Route::resource('sports', \App\Http\Controllers\Admin\SportController::class); // Master sport route
        Route::resource('organisation-sub-types', \App\Http\Controllers\Admin\OrganisationSubTypeController::class);
        Route::resource('languages', \App\Http\Controllers\Admin\LanguageController::class);
        Route::resource('exam-stages', \App\Http\Controllers\Admin\ExamStageController::class)->middleware('can:exam-stages-browse');
        Route::resource('caste-categories', \App\Http\Controllers\Admin\CasteCategoryController::class)->middleware('can:categories-browse');

        // Mentor Management Routes
        Route::prefix('mentor')->name('mentor.')->group(function () {
            Route::resource('languages', \App\Http\Controllers\Admin\MentorLanguageController::class);
            Route::resource('degrees', \App\Http\Controllers\Admin\MentorDegreeController::class);
            Route::resource('industries', \App\Http\Controllers\Admin\MentorIndustryController::class);
            Route::resource('mentee_levels', \App\Http\Controllers\Admin\MentorMenteeLevelController::class);
            Route::get('commissions', [\App\Http\Controllers\Admin\MentorCommissionController::class, 'index'])->name('commissions.index');
            Route::post('commissions', [\App\Http\Controllers\Admin\MentorCommissionController::class, 'store'])->name('commissions.store');

            // All Mentors
            Route::resource('profiles', \App\Http\Controllers\Admin\MentorController::class);

            // Verifications
            Route::get('verifications', [\App\Http\Controllers\Admin\MentorVerificationController::class, 'index'])->name('verifications.index');
            Route::post('verifications/{id}/update/{type}', [\App\Http\Controllers\Admin\MentorVerificationController::class, 'updateStatus'])->name('verifications.update');
        });

        // Organisation Specific Sub-routes
        Route::delete('organisation-awards/{id}', [\App\Http\Controllers\Admin\OrganisationController::class, 'deleteAward'])->name('organisation-awards.destroy');
        Route::delete('organisation-sports/{id}', [\App\Http\Controllers\Admin\OrganisationController::class, 'deleteSport'])->name('organisation-sports.destroy');

        // HR Leaves Module
        Route::prefix('hr')->name('hr.')->group(function () {
            Route::resource('leave-settings', \App\Http\Controllers\Admin\Hr\LeaveSettingController::class);
            Route::resource('leave-policies', \App\Http\Controllers\Admin\Hr\LeavePolicyController::class);
            Route::resource('holidays', \App\Http\Controllers\Admin\Hr\HolidayController::class)->middleware('can:holiday-browse');
            Route::resource('leaves', \App\Http\Controllers\Admin\Hr\LeavesController::class)->middleware('can:leaves-browse');

            // Staff Module Routes
            Route::resource('staff-types', \App\Http\Controllers\Admin\Hr\StaffTypeController::class);
            Route::resource('departments', \App\Http\Controllers\Admin\Hr\DepartmentController::class)->middleware('can:department-browse');
            Route::resource('designations', \App\Http\Controllers\Admin\Hr\DesignationController::class)->middleware('can:designation-browse');
            Route::resource('staff', \App\Http\Controllers\Admin\Hr\StaffController::class)->middleware('can:staff-browse');
            Route::resource('roles', \App\Http\Controllers\Admin\Hr\RoleController::class)->middleware('can:roles-browse');
            
            Route::get('role-assign-rules', [\App\Http\Controllers\Admin\Hr\RoleAssignRuleController::class, 'index'])->name('role-assign-rules.index')->middleware('can:role-assign-rules-browse');
            Route::post('role-assign-rules', [\App\Http\Controllers\Admin\Hr\RoleAssignRuleController::class, 'store'])->name('role-assign-rules.store')->middleware('can:role-assign-rules-edit');

            Route::resource('banks', \App\Http\Controllers\Admin\Hr\BanksController::class);
            Route::post('change-staff-role', [\App\Http\Controllers\Admin\Hr\StaffController::class, 'changeStaffRole'])->name('change-staff-role');

            // Salary Module Routes
            Route::get('attendance', [\App\Http\Controllers\Admin\Hr\AttendanceController::class, 'showAttendance'])->name('attendance.index');
            Route::post('get-at-details', [\App\Http\Controllers\Admin\Hr\AttendanceController::class, 'getAtDetailsForDay'])->name('attendance.details');

            Route::resource('advance', \App\Http\Controllers\Admin\Hr\AdvancePayController::class)->middleware('can:advancepay-browse');
            Route::post('advance/bonus', [\App\Http\Controllers\Admin\Hr\AdvancePayController::class, 'storeBonus'])->name('advance.bonus.store');
            Route::post('get-advance-amount', [\App\Http\Controllers\Admin\Hr\AdvancePayController::class, 'getAdvancePayAmount'])->name('advance.get-amount');

            Route::get('payroll', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'showPayroll'])->name('payroll.index');
            Route::post('payroll/calculate', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'calculateMonthWiseSalary'])->name('payroll.calculate');
            Route::post('payroll/make-payment', [\App\Http\Controllers\Admin\Hr\PayRollController::class, 'makeEmployeePayment'])->name('payroll.make-payment');

            Route::resource('payout', \App\Http\Controllers\Admin\Hr\PayoutController::class)->middleware('can:payout-browse');

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
                Route::resource('project-categories', ProjectCategoryController::class)->middleware('can:categories-browse');
                Route::resource('index', ProjectsController::class); // Main project list
                Route::resource('milestones', MilestoneController::class)->middleware('can:milestone-browse');
                Route::resource('tasks', TaskController::class)->middleware('can:task-browse');
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

        // ==========================================
        // WORK MANAGEMENT ENTERPRISE MODULE
        // ==========================================
        Route::prefix('work-management')->name('work_management.')->group(function () {
            // Dashboards
            Route::get('dashboard', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'overview'])->name('dashboard');
            Route::get('dashboard/overview', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'overview'])->name('dashboard.overview');
            Route::get('dashboard/my-work', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'myWork'])->name('dashboard.my_work');
            Route::get('dashboard/team-work', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'teamWork'])->name('dashboard.team_work');
            Route::get('dashboard/calendar', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'calendar'])->name('dashboard.calendar');
            Route::get('dashboard/calendar-events', [\App\Http\Controllers\Admin\WorkManagement\WorkDashboardController::class, 'calendarEvents'])->name('dashboard.calendar_events');

            // Projects
            Route::resource('projects', \App\Http\Controllers\Admin\WorkManagement\ProjectController::class);
            Route::post('projects/{projectId}/upload-document', [\App\Http\Controllers\Admin\WorkManagement\ProjectController::class, 'uploadDocument'])->name('projects.upload_document');

            // Milestones
            Route::resource('milestones', \App\Http\Controllers\Admin\WorkManagement\MilestoneController::class);
            Route::post('milestones/change-status', [\App\Http\Controllers\Admin\WorkManagement\MilestoneController::class, 'changeStatus'])->name('milestones.change_status');

            // Tasks & Subtasks
            Route::get('tasks/kanban', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'kanban'])->name('tasks.kanban');
            Route::get('task-board', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'kanban'])->name('tasks.task_board');
            Route::match(['get', 'post'], 'tasks/get-project-data', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'getProjectData'])->name('tasks.ajax_project_data');
            Route::post('tasks/change-status', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'changeStatus'])->name('tasks.change_status');
            Route::post('tasks/reassign', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'reassign'])->name('tasks.reassign');
            Route::post('tasks/delegate', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'delegate'])->name('tasks.delegate');
            Route::post('tasks/add-checklist', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'addChecklist'])->name('tasks.add_checklist');
            Route::post('tasks/toggle-checklist', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'toggleChecklist'])->name('tasks.toggle_checklist');
            Route::delete('tasks/delete-checklist/{id}', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'deleteChecklist'])->name('tasks.delete_checklist');
            Route::post('tasks/add-time-entry', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'addTimeEntry'])->name('tasks.add_time_entry');
            Route::post('tasks/add-attachment', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'addAttachment'])->name('tasks.add_attachment');
            Route::delete('tasks/delete-attachment/{id}', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'deleteAttachment'])->name('tasks.delete_attachment');
            Route::post('tasks/add-dependency', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'addDependency'])->name('tasks.add_dependency');
            Route::delete('tasks/delete-dependency/{id}', [\App\Http\Controllers\Admin\WorkManagement\TaskController::class, 'deleteDependency'])->name('tasks.delete_dependency');
            Route::resource('tasks', \App\Http\Controllers\Admin\WorkManagement\TaskController::class);

            // Comments
            Route::post('comments/store', [\App\Http\Controllers\Admin\WorkManagement\TaskCommentController::class, 'store'])->name('comments.store');

            // Teams
            Route::post('teams/get-members', [\App\Http\Controllers\Admin\WorkManagement\TeamController::class, 'getMembersByTeam'])->name('teams.get_members');
            Route::post('teams/{teamId}/add-member', [\App\Http\Controllers\Admin\WorkManagement\TeamController::class, 'addMember'])->name('teams.add_member');
            Route::post('teams/{teamId}/remove-member', [\App\Http\Controllers\Admin\WorkManagement\TeamController::class, 'removeMember'])->name('teams.remove_member');
            Route::resource('teams', \App\Http\Controllers\Admin\WorkManagement\TeamController::class);

            // External Partners & Agencies
            Route::post('partners/{orgId}/add-contact', [\App\Http\Controllers\Admin\WorkManagement\ExternalPartnerController::class, 'addContact'])->name('partners.add_contact');
            Route::post('partners/{orgId}/add-team', [\App\Http\Controllers\Admin\WorkManagement\ExternalPartnerController::class, 'addTeam'])->name('partners.add_team');
            Route::resource('partners', \App\Http\Controllers\Admin\WorkManagement\ExternalPartnerController::class);

            // Meetings
            Route::post('meetings/decision-to-task', [\App\Http\Controllers\Admin\WorkManagement\MeetingController::class, 'convertDecisionToTask'])->name('meetings.decision_to_task');
            Route::resource('meetings', \App\Http\Controllers\Admin\WorkManagement\MeetingController::class);

            // Reports
            Route::get('reports', [\App\Http\Controllers\Admin\WorkManagement\WorkReportController::class, 'index'])->name('reports.index');

            // Project Categories Master
            Route::post('categories/quick-store', [\App\Http\Controllers\Admin\WorkManagement\ProjectCategoryController::class, 'quickStore'])->name('categories.quick_store');
            Route::resource('categories', \App\Http\Controllers\Admin\WorkManagement\ProjectCategoryController::class);

            // Project Types Master
            Route::post('types/quick-store', [\App\Http\Controllers\Admin\WorkManagement\ProjectTypeController::class, 'quickStore'])->name('types.quick_store');
            Route::resource('types', \App\Http\Controllers\Admin\WorkManagement\ProjectTypeController::class);
        });

        // Customer Management Module Routes
        Route::prefix('customers')->name('customers.main.')->group(function () {
            Route::post('import', [CustomerController::class, 'import'])->name('import');
            Route::get('sample-download', [CustomerController::class, 'downloadSample'])->name('sample-download');
            Route::resource('index', CustomerController::class);
            Route::post('get-sub-categories', [CustomerController::class, 'getCategories'])->name('get-sub-categories');
        });

        Route::post('customer-categories/import', [CustomerCategoryController::class, 'import'])->name('customer-categories.import');
        Route::get('customer-categories/sample-download', [CustomerCategoryController::class, 'downloadSample'])->name('customer-categories.sample');
        Route::resource('customer-categories', CustomerCategoryController::class)->names('customer-categories')->middleware('can:customer-category-browse');
        Route::post('quick-add-category', [CustomerCategoryController::class, 'quickStore'])->name('quick-add-category');
        Route::post('quick-add-interest', [InterestedInController::class, 'quickStore'])->name('quick-add-interest');
        Route::post('quick-add-session', [CustomerSessionController::class, 'quickStore'])->name('quick-add-session');
        Route::resource('customer-fields', CustomerFieldController::class)->names('customer-fields')->middleware('can:customer-fields-browse');
        Route::resource('institutes', InstituteController::class)->names('institutes')->middleware('can:institutes-browse');
        Route::resource('interested-ins', InterestedInController::class)->names('interested-ins')->middleware('can:interested-ins-browse');
        Route::resource('customer-sessions', CustomerSessionController::class)->names('customer-sessions')->middleware('can:customer-sessions-browse');

        // Student CRM & Calling Module Routes
        Route::prefix('students-crm')->name('students-crm.')->group(function () {
            Route::post('calling-dashboard/unlock-number', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'unlockNumber'])->name('calling-dashboard.unlock');
            Route::post('calling-dashboard/reassign', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'reassign'])->name('calling-dashboard.reassign');
            Route::post('calling-dashboard/bulk-assign', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'bulkAssign'])->name('calling-dashboard.bulk-assign');
            Route::get('calling-dashboard/customer-history/{id}', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'customerHistory'])->name('calling-dashboard.customer-history');
            Route::get('calling-dashboard/get-locations', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'getLocations'])->name('calling-dashboard.get-locations');
            Route::get('calling-dashboard', [\App\Http\Controllers\Admin\Hr\CallingController::class, 'dashboard'])->name('calling-dashboard.index')->middleware('can:calling-dashboard-browse');
            Route::resource('calling-statuses', CallingStatusController::class)->middleware('can:calling-status-browse');
            Route::post('lead-qualities/toggle-status', [\App\Http\Controllers\Admin\Hr\LeadQualityController::class, 'toggleStatus'])->name('lead-qualities.toggle-status');
            Route::resource('lead-qualities', \App\Http\Controllers\Admin\Hr\LeadQualityController::class)->middleware('can:lead-quality-browse');
            Route::resource('calling-actions', CallingActionController::class)->middleware('can:calling-action-browse');
            Route::get('calling-module', [CallingController::class, 'index'])->name('calling-module.index');
            Route::post('calling-module', [CallingController::class, 'store'])->name('calling-module.store');
            Route::get('calling-module/restart', [CallingController::class, 'restart'])->name('calling-module.restart');
            Route::get('calling-module/get-courses', [CallingController::class, 'getCoursesByProgramLevel'])->name('calling-module.get-courses');
            Route::post('calling-history/import', [CallingController::class, 'importHistory'])->name('calling-history.import')->middleware('can:calling-history-add');
            Route::get('calling-history/sample', [CallingController::class, 'downloadSample'])->name('calling-history.sample')->middleware('can:calling-history-browse');
            Route::get('calling-history', [CallingController::class, 'history'])->name('calling-history.index');
            Route::post('calling-history/history-update-status/{id}', [CallingController::class, 'updateStatus'])->name('calling-history.update-status');
            
            Route::get('lead-assign', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'index'])->name('lead-assign.index')->middleware('can:lead-assign-browse');
            Route::get('lead-assign/get-counts', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'getFilteredCounts'])->name('lead-assign.get-counts')->middleware('can:lead-assign-browse');
            Route::get('lead-assign/batch-details', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'getBatchDetails'])->name('lead-assign.batch-details')->middleware('can:lead-assign-browse');
            Route::post('lead-assign/revoke', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'revokeBatch'])->name('lead-assign.revoke')->middleware('can:lead-assign-browse');
            Route::post('lead-assign', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'store'])->name('lead-assign.store')->middleware('can:lead-assign-add');
            Route::get('lead-assign/{staff}', [\App\Http\Controllers\Admin\Hr\LeadAssignController::class, 'show'])->name('lead-assign.show')->middleware('can:lead-assign-browse');
            
            Route::get('lead-activity-logs', [\App\Http\Controllers\Admin\Hr\LeadActivityLogController::class, 'index'])->name('lead-activity-logs.index')->middleware('can:lead-activity-logs-browse');
        });
        
        Route::resource('target-leads', \App\Http\Controllers\Admin\TargetLeadController::class)->middleware('can:target-leads-browse');

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

        // Billing Module
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::resource('clients', \App\Http\Controllers\Admin\Billing\BillingClientController::class);
            Route::resource('services', \App\Http\Controllers\Admin\Billing\BillingServiceController::class)->middleware('can:home-services-browse');
            Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\Billing\BillingInvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
            Route::resource('invoices', \App\Http\Controllers\Admin\Billing\BillingInvoiceController::class)->middleware('can:billing-invoices-browse');
            Route::resource('payments', \App\Http\Controllers\Admin\Billing\BillingPaymentController::class)->middleware('can:billing-payments-browse');
        });

        // ==========================================
        // ✅ ACCOUNTING & FINANCE MODULE ROUTES
        // ==========================================
        Route::prefix('accounting')->name('accounting.')->group(function () {
            // Dashboard
            Route::get('/', [\App\Http\Controllers\Admin\Accounting\AccountingDashboardController::class, 'index'])->name('dashboard');

            // Chart of Accounts
            Route::resource('chart-of-accounts', \App\Http\Controllers\Admin\Accounting\ChartOfAccountController::class)->names('chart_of_accounts');

            // Parties (Customers, Vendors, Founders, Investors)
            Route::resource('parties', \App\Http\Controllers\Admin\Accounting\PartyController::class);

            // Sales Invoices & Receivables
            Route::post('invoices/{id}/post', [\App\Http\Controllers\Admin\Accounting\SalesInvoiceController::class, 'post'])->name('invoices.post');
            Route::post('invoices/{id}/record-payment', [\App\Http\Controllers\Admin\Accounting\SalesInvoiceController::class, 'recordPayment'])->name('invoices.record_payment');
            Route::get('invoices/{id}/print', [\App\Http\Controllers\Admin\Accounting\SalesInvoiceController::class, 'print'])->name('invoices.print');
            Route::resource('invoices', \App\Http\Controllers\Admin\Accounting\SalesInvoiceController::class);

            // Vendor Bills & Payables
            Route::post('bills/{id}/post', [\App\Http\Controllers\Admin\Accounting\VendorBillController::class, 'post'])->name('bills.post');
            Route::post('bills/{id}/record-payment', [\App\Http\Controllers\Admin\Accounting\VendorBillController::class, 'recordPayment'])->name('bills.record_payment');
            Route::resource('bills', \App\Http\Controllers\Admin\Accounting\VendorBillController::class);

            // Expenses & Approvals
            Route::post('expenses/{id}/submit', [\App\Http\Controllers\Admin\Accounting\ExpenseController::class, 'submit'])->name('expenses.submit');
            Route::post('expenses/{id}/approve', [\App\Http\Controllers\Admin\Accounting\ExpenseController::class, 'approve'])->name('expenses.approve');
            Route::post('expenses/{id}/reject', [\App\Http\Controllers\Admin\Accounting\ExpenseController::class, 'reject'])->name('expenses.reject');
            Route::post('expenses/{id}/pay', [\App\Http\Controllers\Admin\Accounting\ExpenseController::class, 'pay'])->name('expenses.pay');
            Route::resource('expenses', \App\Http\Controllers\Admin\Accounting\ExpenseController::class);

            // Funding & Capital
            Route::post('funding/{id}/post', [\App\Http\Controllers\Admin\Accounting\FundingController::class, 'post'])->name('funding.post');
            Route::resource('funding', \App\Http\Controllers\Admin\Accounting\FundingController::class);

            // Banking & Cash
            Route::post('banking/transfer', [\App\Http\Controllers\Admin\Accounting\BankingController::class, 'transfer'])->name('banking.transfer');
            Route::resource('banking', \App\Http\Controllers\Admin\Accounting\BankingController::class);

            // Manual Journal Vouchers
            Route::post('journals/{id}/reverse', [\App\Http\Controllers\Admin\Accounting\JournalController::class, 'reverse'])->name('journals.reverse');
            Route::resource('journals', \App\Http\Controllers\Admin\Accounting\JournalController::class);

            // Financial Reports & Tax Registers
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'index'])->name('index');
                Route::get('trial-balance', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'trialBalance'])->name('trial_balance');
                Route::get('profit-loss', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'profitAndLoss'])->name('profit_loss');
                Route::get('balance-sheet', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'balanceSheet'])->name('balance_sheet');
                Route::get('general-ledger', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'generalLedger'])->name('general_ledger');
                Route::get('customer-ageing', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'customerAgeing'])->name('customer_ageing');
                Route::get('vendor-ageing', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'vendorAgeing'])->name('vendor_ageing');
                Route::get('gst', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'gstReport'])->name('gst_report');
                Route::get('tds', [\App\Http\Controllers\Admin\Accounting\AccountingReportController::class, 'tdsReport'])->name('tds_report');
            });
        });
    });
});

Route::get('/fix-negative-durations', function() {
    \Illuminate\Support\Facades\DB::table('attendance')->where('duration', '<', 0)->update(['duration' => \Illuminate\Support\Facades\DB::raw('ABS(duration)')]);
    \Illuminate\Support\Facades\DB::table('breaks')->where('duration', '<', 0)->update(['duration' => \Illuminate\Support\Facades\DB::raw('ABS(duration)')]);
    return 'Fixed all negative durations on the live database!';
});

// ==========================================
// ✅ EXPERT PORTAL ROUTES
// ==========================================
Route::group(['prefix' => 'expert', 'as' => 'expert.'], function () {
    Route::get('/login', [\App\Http\Controllers\Expert\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Expert\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Expert\AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth:expert'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Expert\DashboardController::class, 'index'])->name('dashboard');

        // Slot Management
        Route::get('/slots', [\App\Http\Controllers\Expert\SlotController::class, 'index'])->name('slots.index');
        Route::post('/slots', [\App\Http\Controllers\Expert\SlotController::class, 'store'])->name('slots.store');
        Route::put('/slots/{id}', [\App\Http\Controllers\Expert\SlotController::class, 'update'])->name('slots.update');
        Route::delete('/slots/{id}', [\App\Http\Controllers\Expert\SlotController::class, 'destroy'])->name('slots.destroy');

        // Booking & Session Approvals
        Route::get('/bookings', [\App\Http\Controllers\Expert\BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{id}/approve', [\App\Http\Controllers\Expert\BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{id}/reject', [\App\Http\Controllers\Expert\BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{id}/complete', [\App\Http\Controllers\Expert\BookingController::class, 'complete'])->name('bookings.complete');
        Route::post('/bookings/{id}/update-link', [\App\Http\Controllers\Expert\BookingController::class, 'updateMeetingLink'])->name('bookings.update-link');
    });
});

Route::get('/admin/fix-attendance-time', function (\Illuminate\Http\Request $request) {
    // Default to +570 minutes (9.5 hours for USA EDT to IST)
    $minutes = (int) $request->get('minutes', 570);

    // Fix attendance records before Aug 6th
    $count = \Illuminate\Support\Facades\DB::table('attendance')
        ->whereDate('date', '<', '2026-08-06')
        ->update([
            'check_in' => \Illuminate\Support\Facades\DB::raw("DATE_ADD(check_in, INTERVAL $minutes MINUTE)"),
            'check_out' => \Illuminate\Support\Facades\DB::raw("IF(check_out IS NOT NULL, DATE_ADD(check_out, INTERVAL $minutes MINUTE), NULL)")
        ]);

    $breakCount = \Illuminate\Support\Facades\DB::table('breaks')
        ->whereDate('created_at', '<', '2026-08-06')
        ->update([
            'start' => \Illuminate\Support\Facades\DB::raw("DATE_ADD(start, INTERVAL $minutes MINUTE)"),
            'end' => \Illuminate\Support\Facades\DB::raw("IF(end IS NOT NULL, DATE_ADD(end, INTERVAL $minutes MINUTE), NULL)")
        ]);

    return "Fixed $count attendance records and $breakCount break records by adding $minutes minutes!";
});
