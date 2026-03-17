<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Safe Cleanup for Partial Migrations
        $columnsToDrop = [
            'faculty_id', 'slug', 'profile_photo_url', 'cover_photo_url', 'gender', 'date_of_birth', 'short_bio', 'detailed_bio',
            'designation', 'subject_specialization', 'other_qualifications', 'certifications', 'years_of_experience_total',
            'years_of_experience_current_institute', 'previous_institutes', 'industry_experience', 'exams_cleared', 'notable_achievements',
            'current_institute_id', 'current_institute_name', 'faculty_type', 'joining_year', 'courses_taught', 'target_batches',
            'average_batch_size_handled', 'teaching_style', 'language_of_teaching', 'lecture_mode', 'weekly_classes_count',
            'doubt_solving_sessions', 'one_to_one_mentoring', 'years_with_results', 'students_selected_count', 'top_rank_students',
            'best_result_year', 'result_verification_source', 'average_student_feedback_rating', 'intro_video_url', 'demo_lecture_videos',
            'articles_written', 'youtube_channel_url', 'linkedin_profile_url', 'instagram_profile_url', 'telegram_channel_url',
            'total_reviews', 'verified_student_reviews_only', 'student_testimonials', 'peer_reviews', 'awards_recognition',
            'contact_number', 'public_contact_allowed', 'profile_visibility', 'profile_claimed', 'verification_status',
            'meta_title', 'meta_description', 'focus_keywords', 'schema_type', 'canonical_url', 'indexing_status',
            'data_source', 'confidence_score', 'last_updated_on', 'status'
        ];

        foreach ($columnsToDrop as $col) {
            if (Schema::hasColumn('experts', $col)) {
                Schema::table('experts', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        Schema::table('experts', function (Blueprint $table) {
            // 1. Basic Identity
            $table->uuid('faculty_id')->nullable()->after('id'); // Internal ID
            $table->string('slug')->nullable()->after('name');
            $table->string('profile_photo_url')->nullable()->after('img'); // In case they want external URL or just alias
            $table->string('cover_photo_url')->nullable()->after('profile_photo_url');
            $table->string('gender')->nullable()->after('email');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('short_bio', 255)->nullable()->after('date_of_birth');
            $table->text('detailed_bio')->nullable()->after('short_bio');
            $table->string('designation')->nullable()->after('role');
            $table->json('subject_specialization')->nullable()->after('designation');

            // 2. Professional Credentials
            // highest_qualification Exists
            $table->json('other_qualifications')->nullable()->after('highest_qualification');
            $table->json('certifications')->nullable()->after('other_qualifications');
            $table->float('years_of_experience_total')->nullable()->after('exp'); // Numeric version
            $table->float('years_of_experience_current_institute')->nullable()->after('years_of_experience_total');
            $table->json('previous_institutes')->nullable()->after('years_of_experience_current_institute');
            $table->boolean('industry_experience')->default(false)->after('previous_institutes');
            $table->json('exams_cleared')->nullable()->after('industry_experience');
            $table->json('notable_achievements')->nullable()->after('exams_cleared');

            // 3. Institute Association
            $table->unsignedBigInteger('current_institute_id')->nullable()->after('notable_achievements');
            $table->string('current_institute_name')->nullable()->after('current_institute_id');
            $table->string('faculty_type')->nullable()->after('current_institute_name'); // Full-time, etc
            $table->year('joining_year')->nullable()->after('faculty_type');
            $table->json('courses_taught')->nullable()->after('joining_year');
            $table->json('target_batches')->nullable()->after('courses_taught');
            $table->integer('average_batch_size_handled')->nullable()->after('target_batches');

            // 4. Teaching Profile
            $table->string('teaching_style')->nullable()->after('average_batch_size_handled'); // Conceptual, etc
            $table->json('language_of_teaching')->nullable()->after('teaching_style');
            $table->string('lecture_mode')->nullable()->after('language_of_teaching'); // Offline, Online
            $table->integer('weekly_classes_count')->nullable()->after('lecture_mode');
            $table->boolean('doubt_solving_sessions')->default(false)->after('weekly_classes_count');
            $table->boolean('one_to_one_mentoring')->default(false)->after('doubt_solving_sessions');

            // 5. Performance & Impact
            $table->json('years_with_results')->nullable()->after('one_on_one_counseling'); // Overlap check? No, new field
            $table->integer('students_selected_count')->nullable()->after('years_with_results');
            $table->json('top_rank_students')->nullable()->after('students_selected_count');
            $table->year('best_result_year')->nullable()->after('top_rank_students');
            $table->string('result_verification_source')->nullable()->after('best_result_year');
            $table->float('average_student_feedback_rating')->nullable()->after('result_verification_source');

            // 6. Content & Digital Presence
            $table->string('intro_video_url')->nullable()->after('result_verification_source');
            $table->json('demo_lecture_videos')->nullable()->after('intro_video_url');
            $table->json('articles_written')->nullable()->after('demo_lecture_videos');
            $table->string('youtube_channel_url')->nullable()->after('articles_written');
            $table->string('linkedin_profile_url')->nullable()->after('youtube_channel_url');
            $table->string('instagram_profile_url')->nullable()->after('linkedin_profile_url');
            $table->string('telegram_channel_url')->nullable()->after('instagram_profile_url');

            // 7. Ratings, Reviews & Trust
            $table->integer('total_reviews')->default(0)->after('rating');
            $table->boolean('verified_student_reviews_only')->default(false)->after('total_reviews');
            $table->json('student_testimonials')->nullable()->after('verified_student_reviews_only');
            $table->json('peer_reviews')->nullable()->after('student_testimonials');
            $table->json('awards_recognition')->nullable()->after('peer_reviews');

            // 8. Contact & Visibility
            $table->string('contact_number')->nullable()->after('email');
            $table->boolean('public_contact_allowed')->default(false)->after('contact_number');
            $table->string('profile_visibility')->default('Public')->after('public_contact_allowed'); // Public, Institute-only, Private
            $table->boolean('profile_claimed')->default(false)->after('profile_visibility');
            $table->string('verification_status')->default('Pending')->after('profile_claimed'); // Pending, Verified, Rejected

            // 9. SEO & Meta
            $table->string('meta_title')->nullable()->after('verification_status');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('focus_keywords')->nullable()->after('meta_description');
            $table->string('schema_type')->default('Person')->after('focus_keywords');
            $table->string('canonical_url')->nullable()->after('schema_type');
            $table->string('indexing_status')->default('index')->after('canonical_url');

            // 10. Admin
            $table->string('data_source')->default('Manual')->after('indexing_status');
            $table->float('confidence_score')->nullable()->after('data_source');
            $table->timestamp('last_updated_on')->nullable()->after('confidence_score');
            $table->string('status')->default('Active')->after('last_updated_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            $table->dropColumn([
                'faculty_id', 'slug', 'profile_photo_url', 'cover_photo_url', 'gender', 'date_of_birth', 'short_bio', 'detailed_bio',
                'designation', 'subject_specialization', 'other_qualifications', 'certifications', 'years_of_experience_total',
                'years_of_experience_current_institute', 'previous_institutes', 'industry_experience', 'exams_cleared', 'notable_achievements',
                'current_institute_id', 'current_institute_name', 'faculty_type', 'joining_year', 'courses_taught', 'target_batches',
                'average_batch_size_handled', 'teaching_style', 'language_of_teaching', 'lecture_mode', 'weekly_classes_count',
                'doubt_solving_sessions', 'one_to_one_mentoring', 'years_with_results', 'students_selected_count', 'top_rank_students',
                'best_result_year', 'result_verification_source', 'average_student_feedback_rating', 'intro_video_url', 'demo_lecture_videos',
                'articles_written', 'youtube_channel_url', 'linkedin_profile_url', 'instagram_profile_url', 'telegram_channel_url',
                'total_reviews', 'verified_student_reviews_only', 'student_testimonials', 'peer_reviews', 'awards_recognition',
                'contact_number', 'public_contact_allowed', 'profile_visibility', 'profile_claimed', 'verification_status',
                'meta_title', 'meta_description', 'focus_keywords', 'schema_type', 'canonical_url', 'indexing_status',
                'data_source', 'confidence_score', 'last_updated_on', 'status'
            ]);
        });
    }
};
