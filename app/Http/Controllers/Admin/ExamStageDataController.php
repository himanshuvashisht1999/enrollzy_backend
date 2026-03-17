<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamStage;
use App\Models\ExamStageInterview;
use Illuminate\Http\Request;

class ExamStageDataController extends Controller
{
    public function edit(Exam $exam, ExamStage $stage)
    {
        // For now, we only have specialized handling for Interview (ID 3) and Skill Test (ID 5)
        if ($stage->id == 3) {
            $interview = ExamStageInterview::where('exam_id', $exam->id)->first();
            return view('admin.exams.stages.manage_interview', compact('exam', 'stage', 'interview'));
        }

        if ($stage->id == 5) {
            $skill = \App\Models\ExamStageSkill::where('exam_id', $exam->id)->first();
            return view('admin.exams.stages.manage_skill', compact('exam', 'stage', 'skill'));
        }

        if ($stage->id == 4) {
            $medical = \App\Models\ExamStageMedical::where('exam_id', $exam->id)->first();
            return view('admin.exams.stages.manage_medical', compact('exam', 'stage', 'medical'));
        }

        if ($stage->id == 1) {
            $preliminary = \App\Models\ExamStagePreliminary::where('exam_id', $exam->id)->first();
            return view('admin.exams.stages.manage_preliminary', compact('exam', 'stage', 'preliminary'));
        }

        if ($stage->id == 2) {
            $main = \App\Models\ExamStageMain::where('exam_id', $exam->id)->first();
            return view('admin.exams.stages.manage_main', compact('exam', 'stage', 'main'));
        }

        return back()->with('error', 'Specialized management for this stage is coming soon.');
    }

    public function updateInterview(Request $request, Exam $exam)
    {
        $interviewData = $request->all();

        // Handle raw comma separated fields
        $arrayFields = [
            'language_options',
            'documents_required_for_interview_call',
            'interview_process_steps',
            'category_relaxations',
            'payment_modes'
        ];

        foreach ($arrayFields as $field) {
            if ($request->filled($field . '_raw')) {
                $interviewData[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'mandatory',
            'medium_switch_allowed',
            'criteria_weightage_defined',
            'marks_applicable',
            'category_wise_cutoff_applicable',
            'normalization_applied',
            'previous_stage_qualification_required',
            'identity_verification_required',
            'biometric_verification_required',
            'slot_booking_required',
            'rescheduling_allowed',
            'appeal_allowed',
            'appeal_fee_required',
            'pwd_accommodations_available',
            'ex_servicemen_relaxations',
            'interview_fee_required',
            'fee_refundable'
        ];

        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $interviewData[$field] = (bool) $request->input($field);
            }
        }

        $interviewData['last_updated_on'] = now();
        $interviewData['exam_id'] = $exam->id;
        $interviewData['exam_stage_id'] = 3; // Interview

        ExamStageInterview::updateOrCreate(['exam_id' => $exam->id], $interviewData);

    }

    public function updateSkill(Request $request, Exam $exam)
    {
        $skillData = $request->all();

        $arrayFields = ['typing_language_options', 'software_tools_tested', 'documents_required', 'payment_modes'];
        foreach ($arrayFields as $field) {
            if ($request->filled($field . '_raw')) {
                $skillData[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        $booleanFields = [
            'mandatory',
            'backspace_allowed',
            'task_based_evaluation',
            'marks_applicable',
            'pass_fail_only',
            'normalization_applied',
            'previous_stage_qualification_required',
            'assistive_devices_allowed',
            'pwd_accommodations_available',
            'identity_verification_required',
            'retest_allowed',
            'temporary_failure_recovery_allowed',
            'appeal_allowed',
            'appeal_fee_required',
            'mock_test_available',
            'demo_environment_available',
            'skill_test_fee_required',
            'fee_refundable'
        ];

        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $skillData[$field] = (bool) $request->input($field);
            }
        }

        $skillData['last_updated_on'] = now();
        $skillData['exam_id'] = $exam->id;
        $skillData['exam_stage_id'] = 5;

        \App\Models\ExamStageSkill::updateOrCreate(['exam_id' => $exam->id], $skillData);

    }

    public function updateMedical(Request $request, Exam $exam)
    {
        $medicalData = $request->all();

        $arrayFields = [
            'temporary_disqualifications',
            'permanent_disqualifications',
            'medical_exam_procedure_steps',
            'tests_conducted',
            'medical_documents_required',
            'payment_mode'
        ];
        foreach ($arrayFields as $field) {
            if ($request->filled($field . '_raw')) {
                $medicalData[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        $booleanFields = [
            'mandatory',
            'general_health_required',
            'free_from_chronic_diseases',
            'physical_fitness_required',
            'chest_measurement_required',
            'chest_expansion_required',
            'vision_test_required',
            'color_vision_required',
            'night_blindness_disqualifying',
            'spectacles_allowed',
            'hearing_standard_required',
            'speech_standard_required',
            'cardiovascular_system_check',
            'respiratory_system_check',
            'neurological_system_check',
            'musculoskeletal_system_check',
            'mental_health_evaluation_required',
            'fasting_required',
            'medical_review_allowed',
            'appeal_fee_required',
            'temporary_unfit_retest_allowed',
            'slot_booking_required',
            'medical_fee_required',
            'fee_refundable'
        ];
        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $medicalData[$field] = (bool) $request->input($field);
            }
        }

        $medicalData['last_updated_on'] = now();
        $medicalData['exam_id'] = $exam->id;
        $medicalData['exam_stage_id'] = 4;

        \App\Models\ExamStageMedical::updateOrCreate(['exam_id' => $exam->id], $medicalData);

        return redirect()->route('admin.exams.index')->with('success', 'Medical stage details updated successfully.');
    }

    public function updatePreliminary(Request $request, Exam $exam)
    {
        return $this->updateWrittenStage($request, $exam, \App\Models\ExamStagePreliminary::class, 1);
    }

    public function updateMain(Request $request, Exam $exam)
    {
        return $this->updateWrittenStage($request, $exam, \App\Models\ExamStageMain::class, 2);
    }

    protected function updateWrittenStage(Request $request, Exam $exam, $modelClass, $stageId)
    {
        $data = $request->all();

        // Handle raw comma separated fields for arrays
        $arrayFields = ['subjects_required', 'subjects_covered', 'payment_modes_allowed'];
        foreach ($arrayFields as $field) {
            if ($request->filled($field . '_raw')) {
                $data[$field] = array_map('trim', explode(',', $request->input($field . '_raw')));
            }
        }

        // Handle boolean fields
        $booleanFields = [
            'mandatory',
            'gap_year_allowed',
            'negative_marking',
            'normalization_applied',
            'registration_fee_required',
            'late_registration_allowed',
            'security_deposit_required',
            'transaction_charges_applicable'
        ];

        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $data[$field] = (bool) $request->input($field);
            }
        }

        $data['last_updated_on'] = now();
        $data['exam_id'] = $exam->id;
        $data['exam_stage_id'] = $stageId;

        $modelClass::updateOrCreate(['exam_id' => $exam->id], $data);

        $stageName = ($stageId == 1) ? 'Preliminary' : 'Main';
        return redirect()->route('admin.exams.index')->with('success', "$stageName stage details updated successfully.");
    }
}
