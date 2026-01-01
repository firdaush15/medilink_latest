<?php
// app/Http/Controllers/Api/MentalHealthController.php - COMPLETE FIXED VERSION

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MentalHealthAssessment;
use App\Models\AssessmentAnswer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MentalHealthController extends Controller
{
    /**
     * ✅ FIXED: Store mental health assessment result
     */
    public function storeAssessment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_score' => 'required|integer',
            'risk_level' => 'required|in:good,mild,moderate,severe',
            'recommendations' => 'required|array',
            'answers' => 'required|array',
            'answers.*.question_number' => 'required|integer',
            'answers.*.question_text' => 'required|string',
            'answers.*.answer_option' => 'required|string',
            'answers.*.score_value' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            Log::info('=== MENTAL HEALTH ASSESSMENT SAVE ===');
            Log::info('User ID: ' . $request->user_id);
            Log::info('Total Score: ' . $request->total_score);
            Log::info('Risk Level: ' . $request->risk_level);

            // ✅ Get or create patient record
            $patient = Patient::where('user_id', $request->user_id)->first();
            
            if (!$patient) {
                Log::warning("No patient record found for user_id: {$request->user_id}. Creating one now...");
                
                $user = User::find($request->user_id);
                
                $patient = Patient::create([
                    'user_id' => $request->user_id,
                    'phone_number' => '0000000000', // Placeholder
                    'gender' => 'Other',
                    'date_of_birth' => '2000-01-01', // Placeholder
                ]);
                
                Log::info("✅ Patient record created: patient_id = {$patient->patient_id}");
            }

            Log::info("Patient ID: {$patient->patient_id}");

            // ✅ FIX: Convert recommendations array to JSON string
            $recommendations = $request->recommendations;
            if (is_array($recommendations)) {
                $recommendations = json_encode($recommendations);
            }

            // Create assessment
            $assessment = MentalHealthAssessment::create([
                'patient_id' => $patient->patient_id,
                'assessment_type' => 'mental_health',
                'total_score' => $request->total_score,
                'risk_level' => $request->risk_level,
                'recommendations' => $recommendations, // ✅ Now it's a JSON string
                'assessment_date' => now(),
                'is_shared_with_doctor' => true,
            ]);

            Log::info("✅ Assessment created: assessment_id = {$assessment->assessment_id}");

            // Store individual answers
            foreach ($request->answers as $answer) {
                AssessmentAnswer::create([
                    'assessment_id' => $assessment->assessment_id,
                    'question_number' => $answer['question_number'],
                    'question_text' => $answer['question_text'],
                    'answer_option' => $answer['answer_option'],
                    'score_value' => $answer['score_value'],
                ]);
            }

            Log::info("✅ " . count($request->answers) . " answers stored");

            DB::commit();

            // ✅ Return response with properly formatted data
            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully',
                'assessment' => [
                    'assessment_id' => $assessment->assessment_id,
                    'total_score' => $assessment->total_score,
                    'risk_level' => $assessment->risk_level,
                    'risk_level_display' => $assessment->risk_level_display,
                    'risk_color' => $assessment->risk_color,
                    'recommendations' => json_decode($recommendations, true), // Return as array
                    'assessment_date' => $assessment->assessment_date->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ ASSESSMENT SAVE FAILED: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get patient's assessment history
     */
    public function getPatientAssessments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            Log::info('=== FETCHING ASSESSMENT HISTORY ===');
            Log::info('User ID: ' . $request->user_id);

            $patient = Patient::where('user_id', $request->user_id)->first();
            
            if (!$patient) {
                Log::warning("No patient record found for user_id: {$request->user_id}");
                
                return response()->json([
                    'success' => true,
                    'assessments' => [],
                    'total_count' => 0,
                    'message' => 'No assessments found. Take your first assessment!'
                ], 200);
            }

            Log::info("Patient ID: {$patient->patient_id}");

            $assessments = MentalHealthAssessment::where('patient_id', $patient->patient_id)
                ->orderBy('assessment_date', 'desc')
                ->get()
                ->map(function ($assessment) {
                    // ✅ Properly decode JSON recommendations
                    $recommendations = $assessment->recommendations;
                    if (is_string($recommendations)) {
                        $recommendations = json_decode($recommendations, true);
                    }

                    return [
                        'assessment_id' => $assessment->assessment_id,
                        'total_score' => $assessment->total_score,
                        'risk_level' => $assessment->risk_level,
                        'risk_level_display' => $assessment->risk_level_display,
                        'risk_color' => $assessment->risk_color,
                        'recommendations' => $recommendations,
                        'assessment_date' => $assessment->assessment_date->format('M d, Y'),
                        'assessment_time' => $assessment->assessment_date->format('h:i A'),
                        'is_reviewed' => $assessment->isReviewed(),
                        'doctor_notes' => $assessment->doctor_notes,
                    ];
                });

            Log::info("✅ Found {$assessments->count()} assessments");

            return response()->json([
                'success' => true,
                'assessments' => $assessments,
                'total_count' => $assessments->count(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ FETCH HISTORY FAILED: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assessments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get assessment details with answers
     */
    public function getAssessmentDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assessment_id' => 'required|exists:mental_health_assessments,assessment_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assessment = MentalHealthAssessment::with(['answers', 'reviewedByDoctor.user'])
                ->find($request->assessment_id);

            if (!$assessment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment not found'
                ], 404);
            }

            // ✅ Properly decode recommendations
            $recommendations = $assessment->recommendations;
            if (is_string($recommendations)) {
                $recommendations = json_decode($recommendations, true);
            }

            return response()->json([
                'success' => true,
                'assessment' => [
                    'assessment_id' => $assessment->assessment_id,
                    'total_score' => $assessment->total_score,
                    'risk_level' => $assessment->risk_level,
                    'risk_level_display' => $assessment->risk_level_display,
                    'risk_color' => $assessment->risk_color,
                    'recommendations' => $recommendations,
                    'assessment_date' => $assessment->assessment_date->format('M d, Y h:i A'),
                    'is_reviewed' => $assessment->isReviewed(),
                    'doctor_notes' => $assessment->doctor_notes,
                    'reviewed_by' => $assessment->reviewedByDoctor ? $assessment->reviewedByDoctor->user->name : null,
                    'reviewed_at' => $assessment->reviewed_at ? $assessment->reviewed_at->format('M d, Y h:i A') : null,
                    'answers' => $assessment->answers->map(function ($answer) {
                        return [
                            'question_number' => $answer->question_number,
                            'question_text' => $answer->question_text,
                            'answer_option' => $answer->answer_option,
                            'score_value' => $answer->score_value,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assessment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get assessment statistics for charts
     */
    public function getAssessmentStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'period' => 'nullable|in:week,month,3months,6months,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $patient = Patient::where('user_id', $request->user_id)->first();
            
            if (!$patient) {
                return response()->json([
                    'success' => true,
                    'statistics' => [
                        'total_assessments' => 0,
                        'average_score' => 0,
                        'latest_score' => 0,
                        'trend' => 'stable',
                        'trend_value' => 0,
                    ],
                    'chart_data' => [],
                    'risk_distribution' => [],
                ], 200);
            }

            // Determine date range
            $period = $request->period ?? 'month';
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                '3months' => now()->subMonths(3),
                '6months' => now()->subMonths(6),
                'year' => now()->subYear(),
                default => now()->subMonth(),
            };

            $assessments = MentalHealthAssessment::where('patient_id', $patient->patient_id)
                ->where('assessment_date', '>=', $startDate)
                ->orderBy('assessment_date', 'asc')
                ->get();

            // Prepare chart data
            $chartData = $assessments->map(function ($assessment) {
                return [
                    'date' => $assessment->assessment_date->format('M d'),
                    'score' => $assessment->total_score,
                    'risk_level' => $assessment->risk_level,
                ];
            });

            // Calculate statistics
            $avgScore = $assessments->avg('total_score');
            $latestScore = $assessments->last()?->total_score;
            $firstScore = $assessments->first()?->total_score;
            $trend = $latestScore && $firstScore ? ($latestScore - $firstScore) : 0;

            // Risk level distribution
            $riskDistribution = $assessments->groupBy('risk_level')->map(fn($group) => $group->count());

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total_assessments' => $assessments->count(),
                    'average_score' => round($avgScore, 1),
                    'latest_score' => $latestScore ?? 0,
                    'trend' => $trend > 0 ? 'declining' : ($trend < 0 ? 'improving' : 'stable'),
                    'trend_value' => abs($trend),
                ],
                'chart_data' => $chartData,
                'risk_distribution' => $riskDistribution,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}