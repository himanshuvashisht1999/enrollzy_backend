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

        Schema::table('experts', function (Blueprint $table) {});
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
