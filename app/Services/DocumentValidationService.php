<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentValidationService
{
    /**
     * The OCR Space API Key.
     * Use 'helloworld' for simple testing or get a free key from https://ocr.space/ocrapi
     */
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.ocr_space.key');
    }

    /**
     * Validate a document based on its type and content keywords.
     *
     * @param string $filePath Absolute path to the image
     * @param string $type cr, or, license, id
     * @return array ['success' => bool, 'message' => string]
     */
    public function validate($filePath, $type)
    {
        try {
            // 1. Perform OCR
            $response = Http::attach(
                'file', file_get_contents($filePath), basename($filePath)
            )->post('https://api.ocr.space/parse/image', [
                'apikey' => $this->apiKey,
                'language' => 'eng',
                'isOverlayRequired' => false,
                'detectOrientation' => true,
                'scale' => true,
            ]);

            if (!$response->successful()) {
                Log::error('OCR Service Failed', ['status' => $response->status()]);
                return ['success' => false, 'message' => 'Validation service currently unavailable.'];
            }

            $data = $response->json();
            
            // Check for API errors (over limit, etc.)
            if (isset($data['OCRExitCode']) && $data['OCRExitCode'] > 1) {
                return ['success' => false, 'message' => 'Scan failed: ' . ($data['ErrorMessage'][0] ?? 'Unknown error')];
            }

            $text = strtoupper($data['ParsedResults'][0]['ParsedText'] ?? '');

            if (empty($text) || strlen($text) < 10) {
                return ['success' => false, 'message' => 'Could not read document. Please ensure the image is clear and well-lit.'];
            }

            // 2. Strict Keyword Matching
            $config = $this->getKeywordsForType($type);
            $required = $config['required'] ?? [];
            $supporting = $config['supporting'] ?? [];
            
            // Check Required Phrases (Must match at least one)
            $hasRequired = false;
            foreach ($required as $phrase) {
                if (str_contains($text, strtoupper($phrase))) {
                    $hasRequired = true;
                    break;
                }
            }

            if (!empty($required) && !$hasRequired) {
                return [
                    'success' => false,
                    'message' => "This doesn't look like a valid " . strtoupper(str_replace('_file', '', $type)) . ". Please upload the correct document."
                ];
            }

            // Count Supporting Keywords
            $matchCount = 0;
            foreach ($supporting as $keyword) {
                if (str_contains($text, strtoupper($keyword))) {
                    $matchCount++;
                }
            }

            // We need at least 2 supporting keywords or 1 required phrase match
            if (!$hasRequired && $matchCount < 2) {
                return [
                    'success' => false,
                    'message' => "Validation failed. The document content doesn't match the expected " . strtoupper(str_replace('_file', '', $type)) . " format."
                ];
            }

            return [
                'success' => true,
                'message' => 'Document validated successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('Document Validation Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Connection to validation service failed.'];
        }
    }

    protected function getKeywordsForType($type)
    {
        return match ($type) {
            'cr_file' => [
                'required' => ['CERTIFICATE OF REGISTRATION', 'REGISTRATION'],
                'supporting' => ['MV FILE NO', 'CHASSIS', 'ENGINE NO', 'PLATE NO', 'DENOMINATION', 'MODEL', 'PHILIPPINES', 'TRANSPORTATION']
            ],
            'or_file' => [
                'required' => ['OFFICIAL RECEIPT', 'LTO'],
                'supporting' => ['PAYOR', 'AMOUNT PAID', 'TOTAL', 'CASHIER', 'DATE', 'RECEIPT NO', 'PHILIPPINES', 'TRANSPORTATION']
            ],
            'license_file' => [
                'required' => ['DRIVER', 'LICENSE'],
                'supporting' => ['RESTRICTIONS', 'EXPIRY', 'ADDRESS', 'NATIONALITY', 'BIRTH', 'WEIGHT', 'HEIGHT', 'PHILIPPINES']
            ],
            'cor_file', 'com_file' => [
                'required' => ['MATRICULATION', 'ENROLLMENT', 'EVSU'],
                'supporting' => ['SEMESTER', 'SCHOOL YEAR', 'SUBJECTS', 'UNITS', 'TOTAL FEES', 'ASSESSMENT']
            ],
            'student_id_file', 'employee_id_file' => [
                'required' => ['EVSU', 'UNIVERSITY'],
                'supporting' => ['STUDENT', 'FACULTY', 'EMPLOYEE', 'VALID', 'ID NO', 'IDENTIFICATION', 'NAME']
            ],
            default => [
                'required' => [],
                'supporting' => []
            ],
        };
    }

}
