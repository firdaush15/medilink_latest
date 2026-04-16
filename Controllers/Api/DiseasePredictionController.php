<?php
// app/Http/Controllers/Api/DiseasePredictionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\DiseasePredictionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DiseasePredictionController extends Controller
{
    private string $mlApiUrl;

    public function __construct()
    {
        $this->mlApiUrl = env('ML_API_URL', 'http://127.0.0.1:5000');
    }

    // =========================================================================
    // GET /api/prediction/symptoms
    // =========================================================================
    public function getSymptoms()
    {
        try {
            $response = Http::timeout(10)->get("{$this->mlApiUrl}/symptoms");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'error'   => 'Failed to fetch symptoms from ML API',
            ], 502);
        } catch (\Exception $e) {
            Log::error('ML API symptoms fetch failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'ML service unavailable'], 503);
        }
    }

    // =========================================================================
    // POST /api/prediction/predict   — initial prediction
    //
    // Body: {
    //   "user_id": 1,
    //   "symptoms": ["symptom_fever", "symptom_cough", ...],
    //   "duration": "1-3 days",      // optional
    //   "round": 1,                  // optional, default 1
    //   "patient_context": {...}     // optional
    // }
    //
    // Response A (confident):   { needs_clarification: false, prediction, confidence, ... }
    // Response B (uncertain):   { needs_clarification: true,  follow_up_questions, ... }
    // =========================================================================
    public function predict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|integer|exists:users,id',
            'symptoms'   => 'required|array|min:1',
            'symptoms.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $patient = $this->getPatientFromUserId($request->user_id);
        if (!$patient) {
            return response()->json(['success' => false, 'error' => 'Patient not found'], 404);
        }

        // Build patient context from DB (secure — never trust client-sent context alone)
        $patientContext = [
            'gender'             => $patient->gender,
            'date_of_birth'      => $patient->date_of_birth?->toDateString(),
            'blood_type'         => $patient->blood_type,
            'chronic_conditions' => $patient->chronic_conditions,
            'smoking'            => $patient->smoking,
            'alcohol'            => $patient->alcohol,
        ];

        try {
            $mlPayload = [
                'symptoms'        => $request->symptoms,
                'patient_context' => $patientContext,
            ];

            // Forward optional fields
            if ($request->has('duration'))  $mlPayload['duration'] = $request->duration;
            if ($request->has('round'))     $mlPayload['round']    = $request->round;

            $mlResponse = Http::timeout(20)->post("{$this->mlApiUrl}/predict", $mlPayload);
            $result     = $mlResponse->json();

            if ($mlResponse->serverError()) {
                return response()->json([
                    'success' => false,
                    'error'   => 'ML service is currently unavailable.',
                ], 503);
            }

            // Save to history ONLY when we have a confident final result
            if (($result['success'] ?? false) && isset($result['prediction']) && !($result['needs_clarification'] ?? false)) {
                DiseasePredictionHistory::create([
                    'patient_id'      => $patient->patient_id,
                    'symptoms'        => json_encode($request->symptoms),
                    'prediction'      => $result['prediction'],
                    'confidence'      => $result['confidence'],
                    'top_predictions' => json_encode($result['top_predictions']),
                    'risk_level'      => $result['risk_level'],
                    'recommendation'  => $result['recommendation'],
                ]);
            }

            return response()->json($result, $mlResponse->status());

        } catch (\Exception $e) {
            Log::error('ML Prediction failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Prediction service unavailable.'], 503);
        }
    }

    // =========================================================================
    // POST /api/prediction/clarify   — active diagnosis round 2+
    //
    // Body: {
    //   "user_id": 1,
    //   "original_symptoms": [...],
    //   "new_answers": {
    //     "symptom_cough": true,
    //     "symptom_fever": false
    //   },
    //   "round": 2,
    //   "duration": "1-3 days"       // optional, forwarded to ML
    // }
    // =========================================================================
    public function clarify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|integer|exists:users,id',
            'original_symptoms' => 'required|array',
            'new_answers'       => 'required|array',
            'round'             => 'required|integer|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $patient = $this->getPatientFromUserId($request->user_id);
        if (!$patient) {
            return response()->json(['success' => false, 'error' => 'Patient not found'], 404);
        }

        try {
            $mlPayload = [
                'original_symptoms' => $request->original_symptoms,
                'new_answers'       => $request->new_answers,
                'round'             => $request->round,
            ];

            if ($request->has('duration')) $mlPayload['duration'] = $request->duration;

            $mlResponse = Http::timeout(20)->post("{$this->mlApiUrl}/clarify", $mlPayload);
            $result     = $mlResponse->json();

            if ($mlResponse->serverError()) {
                return response()->json(['success' => false, 'error' => 'ML service unavailable.'], 503);
            }

            // Save to history when clarification produces a final confident result
            if (($result['success'] ?? false) && isset($result['prediction']) && !($result['needs_clarification'] ?? false)) {
                DiseasePredictionHistory::create([
                    'patient_id'      => $patient->patient_id,
                    'symptoms'        => json_encode($result['accumulated_symptoms'] ?? $request->original_symptoms),
                    'prediction'      => $result['prediction'],
                    'confidence'      => $result['confidence'],
                    'top_predictions' => json_encode($result['top_predictions']),
                    'risk_level'      => $result['risk_level'],
                    'recommendation'  => $result['recommendation'],
                ]);
            }

            return response()->json($result, $mlResponse->status());

        } catch (\Exception $e) {
            Log::error('ML Clarify failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Clarification service unavailable.'], 503);
        }
    }

    // =========================================================================
    // POST /api/prediction/history
    // =========================================================================
    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $patient = $this->getPatientFromUserId($request->user_id);
        if (!$patient) {
            return response()->json(['success' => false, 'error' => 'Patient not found'], 404);
        }

        $history = DiseasePredictionHistory::where('patient_id', $patient->patient_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id'              => $item->id,
                    'symptoms'        => json_decode($item->symptoms, true),
                    'prediction'      => $item->prediction,
                    'prediction_display' => str_replace('_', ' ', $item->prediction),
                    'confidence'      => $item->confidence,
                    'top_predictions' => json_decode($item->top_predictions, true),
                    'risk_level'      => $item->risk_level,
                    'recommendation'  => $item->recommendation,
                    'date'            => $item->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json(['success' => true, 'history' => $history]);
    }

    // =========================================================================
    // Helper
    // =========================================================================
    private function getPatientFromUserId(int $userId): ?Patient
    {
        $user = \App\Models\User::with('patient')->find($userId);
        return $user?->patient;
    }
}