<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class TaxInvoiceExtractionService
{
    /**
     * Extract tax invoice number and date from uploaded file using Gemini AI
     *
     * @param string $filePath Absolute path to the file
     * @return array|null ['number' => string, 'date' => string] or null if failed
     */
    public function extractFromFile(string $filePath): ?array
    {
        // HYBRID APPROACH: Try regex extraction first, then AI fallback
        
        // Step 1: Try direct PDF text extraction with regex (FAST & RELIABLE)
        $regexResult = $this->extractWithRegex($filePath);
        if ($regexResult) {
            Log::info('Tax invoice extraction successful (REGEX)', $regexResult);
            return $regexResult;
        }
        
        // Step 2: Fallback to AI extraction (SLOWER but handles complex cases)
        Log::info('Regex extraction failed, trying AI extraction');
        return $this->extractWithAI($filePath);
    }
    
    /**
     * Extract using regex pattern matching (FAST, works for text-based PDFs)
     */
    protected function extractWithRegex(string $filePath): ?array
    {
        try {
            // Check if PDF parser is available
            if (!class_exists('\Smalot\PdfParser\Parser')) {
                Log::info('PDF Parser not available, skipping regex extraction');
                return null;
            }
            
            Log::info('Starting regex extraction', ['file' => basename($filePath)]);
            
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            Log::info('PDF text extracted', ['length' => strlen($text), 'preview' => substr($text, 0, 500)]);
            
            if (empty($text)) {
                Log::info('No text extracted from PDF');
                return null;
            }
            
            // Pattern for Coretax format: 17-18 alphanumeric characters
            // Example: 08022500371597228 (17 digits), 080225003715972Z8 (18 chars with letter)
            // Pattern: DDMMYYXXXXXXXXXXX (DD=kode, MM=bulan, YY=tahun, X=nomor seri)
            $coretaxPattern = '/(?:Kode dan Nomor Seri Faktur Pajak|Nomor Faktur|No\.\s*Faktur|NSFP)[\s:]+([0-9]{2}[0-9]{2}[0-9]{2}[0-9A-Z]{11,12})/i';
            
            // Pattern for old e-Faktur format: 010.000-YY.XXXXXXXX
            $eFakturPattern = '/(?:Nomor Faktur|No\.\s*Faktur)[\s:]+(\d{3}\.\d{3}-\d{2}\.\d{8})/i';
            
            $number = null;
            
            // Try Coretax pattern first
            if (preg_match($coretaxPattern, $text, $matches)) {
                $number = $matches[1];
                Log::info('Found Coretax format number', ['number' => $number, 'full_match' => $matches[0]]);
            }
            // Try e-Faktur pattern
            elseif (preg_match($eFakturPattern, $text, $matches)) {
                $number = $matches[1];
                Log::info('Found e-Faktur format number', ['number' => $number, 'full_match' => $matches[0]]);
            } else {
                Log::warning('No invoice number pattern matched', ['text_sample' => substr($text, 0, 1000)]);
            }
            
            // Extract date
            $date = null;
            
            // Try various date patterns
            $datePatterns = [
                '/(\d{2}\/\d{2}\/\d{4})/',           // DD/MM/YYYY
                '/(\d{4}-\d{2}-\d{2})/',             // YYYY-MM-DD
                '/(\d{2}-\d{2}-\d{4})/',             // DD-MM-YYYY
                '/(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+(\d{4})/i', // DD Month YYYY
            ];
            
            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    if (count($matches) == 4) {
                        // Indonesian month format
                        $months = [
                            'januari' => '01', 'februari' => '02', 'maret' => '03',
                            'april' => '04', 'mei' => '05', 'juni' => '06',
                            'juli' => '07', 'agustus' => '08', 'september' => '09',
                            'oktober' => '10', 'november' => '11', 'desember' => '12'
                        ];
                        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                        $month = $months[strtolower($matches[2])];
                        $year = $matches[3];
                        $date = "{$year}-{$month}-{$day}";
                        Log::info('Found date (Indonesian format)', ['date' => $date, 'original' => $matches[0]]);
                    } else {
                        $date = $matches[1];
                        // Convert to YYYY-MM-DD if needed
                        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $date, $dateParts)) {
                            $date = "{$dateParts[3]}-{$dateParts[2]}-{$dateParts[1]}";
                        } elseif (preg_match('/(\d{2})-(\d{2})-(\d{4})/', $date, $dateParts)) {
                            $date = "{$dateParts[3]}-{$dateParts[2]}-{$dateParts[1]}";
                        }
                        Log::info('Found date (standard format)', ['date' => $date, 'original' => $matches[0]]);
                    }
                    break;
                    break;
                }
            }
            
            if (!$date) {
                Log::warning('No date pattern matched in regex');
            }

            // Extract Value for Validation (Requested: Harga Jual / Penggantian / Uang Muka / Termin)
            // Note: We map this to 'dpp' key for compatibility with controller validation logic
            $dpp = null;
            
            // Pattern 1: "Harga Jual/Penggantian/Uang Muka/Termin"
            // This is the specific line requested by user
            if (preg_match('/Harga Jual[^0-9]*?((?:Rp\.?\s*)?[\d\.]+(?:,\d{1,2})?)/i', $text, $dppMatches)) {
                $rawNum = preg_replace('/[^0-9,]/', '', $dppMatches[1]);
                $dpp = (float) str_replace(',', '.', str_replace('.', '', $rawNum));
            }
            // Pattern 2: "Dasar Pengenaan Pajak" (Fallback if above not found)
            elseif (preg_match('/Dasar Pengenaan Pajak[^0-9]*?((?:Rp\.?\s*)?[\d\.]+(?:,\d{1,2})?)/i', $text, $dppMatches)) {
                $rawNum = preg_replace('/[^0-9,]/', '', $dppMatches[1]); 
                $dpp = (float) str_replace(',', '.', str_replace('.', '', $rawNum)); 
            } 
            // Pattern 3: "Total Nilai Jual" (Fallback)
            elseif (preg_match('/Total Nilai Jual[^0-9]*?((?:Rp\.?\s*)?[\d\.]+(?:,\d{1,2})?)/i', $text, $dppMatches)) {
                $rawNum = preg_replace('/[^0-9,]/', '', $dppMatches[1]);
                $dpp = (float) str_replace(',', '.', str_replace('.', '', $rawNum));
            }

            // Extract Reference Number (Invoice Number)
            $reference = null;
            if (preg_match('/[A-Z0-9\-\/]*\/VPN\/[A-Z0-9\-\/]*/', $text, $refMatches)) {
                $reference = $refMatches[0];
                Log::info('Found reference number', ['reference' => $reference]);
            }

            // Return if we found both number and date
            if ($number && $date) {
                Log::info('Regex extraction SUCCESSFUL', [
                    'number' => $number, 
                    'date' => $date,
                    'dpp' => $dpp, // This now contains "Harga Jual" value
                    'reference' => $reference
                ]);
                return [
                    'number' => $number,
                    'date' => $date,
                    'dpp' => $dpp,
                    'reference' => $reference
                ];
            }
            
            Log::info('Regex extraction incomplete', ['number' => $number, 'date' => $date]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Regex extraction exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    
    /**
     * Extract using Gemini AI (FALLBACK, handles complex cases)
     */
    protected function extractWithAI(string $filePath): ?array
    {
        $apiKey = config('ai_assistant.api_key');
        $model = config('ai_assistant.model', 'gemini-1.5-flash');

        if (!$apiKey) {
            Log::warning('Gemini API key not configured');
            return null;
        }

        try {
            // Convert file to base64
            $fileContent = file_get_contents($filePath);
            $base64 = base64_encode($fileContent);
            
            // Detect MIME type
            $mimeType = mime_content_type($filePath);
            
            // Prepare prompt for Gemini - Updated for Coretax DJP 2025
            $prompt = "Anda adalah AI yang bertugas mengekstrak informasi dari faktur pajak Indonesia.\n\n";
            $prompt .= "KONTEKS: Indonesia menggunakan aplikasi Coretax DJP sejak 2025 (sebelumnya e-Faktur).\n\n";
            $prompt .= "Tugas Anda:\n";
            $prompt .= "1. Ekstrak NOMOR FAKTUR PAJAK (NSFP/Kode dan Nomor Seri Faktur Pajak)\n";
            $prompt .= "   FORMAT YANG MUNGKIN:\n";
            $prompt .= "   a) Format CORETAX BARU (2025+): 18 karakter alphanumeric tanpa pemisah\n";
            $prompt .= "      Contoh: 080225003715972Z8, 010125001234567A9\n";
            $prompt .= "      Pattern: DDMMYYXXXXXXXXXXX (DD=kode, MM=bulan, YY=tahun, X=nomor seri, bisa ada huruf)\n";
            $prompt .= "   b) Format e-Faktur LAMA (sebelum 2025): 16 digit dengan titik dan strip\n";
            $prompt .= "      Contoh: 010.000-24.12345678\n";
            $prompt .= "      Pattern: XXX.XXX-YY.XXXXXXXX\n";
            $prompt .= "   CARI TEXT DEKAT: 'Kode dan Nomor Seri Faktur Pajak', 'Nomor Faktur', 'No. Faktur', 'NSFP'\n\n";
            $prompt .= "2. Ekstrak TANGGAL FAKTUR PAJAK\n";
            $prompt .= "   - Format: DD/MM/YYYY atau YYYY-MM-DD atau DD-MM-YYYY\n";
            $prompt .= "   - Cari text dekat: 'Tanggal Faktur', 'Tgl Faktur', 'Tanggal', 'Date'\n";
            $prompt .= "   - Bisa juga di bagian bawah dekat QR code atau tanda tangan\n\n";
            $prompt .= "3. Ekstrak HARGA JUAL / PENGGANTIAN (Bukan DPP)\n";
            $prompt .= "   - Cari angka di baris 'Harga Jual / Penggantian / Uang Muka / Termin'\n";
            $prompt .= "   - Ambil angkanya saja (contoh: 10000000)\n";
            $prompt .= "   - Simpan dalam field 'dpp' (meskipun ini Harga Jual)\n\n";
            $prompt .= "4. Ekstrak NOMOR REFERENSI (INVOICE)\n";
            $prompt .= "   - Cari string yang mengandung '/VPN/' (contoh: 001/VPN/XI/2025)\n\n";
            $prompt .= "PENTING:\n";
            $prompt .= "- Nomor faktur Coretax biasanya 18 karakter (bisa ada huruf di akhir)\n";
            $prompt .= "- Nomor faktur e-Faktur lama 16 digit (hanya angka dengan pemisah)\n";
            $prompt .= "- Prioritaskan nomor yang ada di bagian 'Kode dan Nomor Seri Faktur Pajak'\n";
            $prompt .= "- Jika tidak menemukan, return null\n";
            $prompt .= "- Tanggal harus valid dan dalam format standar\n\n";
            $prompt .= "Response format (JSON only, no explanation):\n";
            $prompt .= "Untuk Coretax: {\"number\": \"080225003715972Z8\", \"date\": \"2025-11-14\", \"dpp\": 10000000, \"reference\": \"001/VPN/XI/2025\"}\n";
            $prompt .= "Untuk e-Faktur: {\"number\": \"010.000-24.12345678\", \"date\": \"2024-11-21\", \"dpp\": 5000000, \"reference\": \"INV/VPN/2024/001\"}";

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 200,
                ]
            ];

            // Retry logic: 2 attempts
            $maxAttempts = 2;
            $attempt = 0;
            
            while ($attempt < $maxAttempts) {
                $attempt++;
                
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-goog-api-key' => $apiKey
                    ])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    
                    if ($text) {
                        // Try to parse JSON from response
                        $extracted = $this->parseExtractionResponse($text);
                        
                        if ($extracted) {
                            Log::info('Tax invoice extraction successful (AI)', $extracted);
                            return $extracted;
                        }
                    }
                }
                
                // If first attempt failed, wait 2 seconds before retry
                if ($attempt < $maxAttempts) {
                    sleep(2);
                }
            }
            
            Log::warning('Tax invoice extraction failed after retries');
            return null;
            
        } catch (\Exception $e) {
            Log::error('Tax invoice extraction error', [
                'message' => $e->getMessage(),
                'file' => $filePath
            ]);
            return null;
        }
    }

    /**
     * Parse extraction response from Gemini
     */
    protected function parseExtractionResponse(string $text): ?array
    {
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);
        
        try {
            $data = json_decode($text, true);
            
            if (isset($data['number']) && isset($data['date'])) {
                // Validate and format
                $number = trim($data['number']);
                $date = trim($data['date']);
                
                // Convert date to Y-m-d format if needed
                if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $date, $matches)) {
                    $date = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                }
                
                return [
                    'number' => $number,
                    'date' => $date
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse extraction response', ['text' => $text]);
        }
        
        return null;
    }

    /**
     * Compress and store uploaded file
     *
     * @param UploadedFile $file
     * @param int $invoiceId
     * @return string Storage path
     */
    public function compressAndStore(UploadedFile $file, int $invoiceId): string
    {
        $year = now()->year;
        $extension = $file->getClientOriginalExtension();
        $filename = "{$invoiceId}.{$extension}";
        $directory = "tax-invoices/{$year}";
        
        // Ensure directory exists
        Storage::makeDirectory($directory);
        
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            // Compress image
            return $this->compressImage($file, $directory, $filename);
        } else {
            // For PDF, just store (Laravel doesn't have built-in PDF compression)
            // In production, you could use ghostscript or similar
            $path = $file->storeAs($directory, $filename);
            return $path;
        }
    }

    /**
     * Compress image file
     */
    protected function compressImage(UploadedFile $file, string $directory, string $filename): string
    {
        try {
            // Check if Intervention Image is available
            if (!class_exists('Intervention\Image\Facades\Image')) {
                // Fallback: just store without compression
                return $file->storeAs($directory, $filename);
            }
            
            $image = Image::make($file);
            
            // Resize if too large (max 1200px width)
            if ($image->width() > 1200) {
                $image->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            
            // Convert to WebP for better compression
            $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
            $fullPath = storage_path("app/{$directory}/{$webpFilename}");
            
            $image->encode('webp', 80)->save($fullPath);
            
            return "{$directory}/{$webpFilename}";
            
        } catch (\Exception $e) {
            Log::warning('Image compression failed, storing original', ['error' => $e->getMessage()]);
            // Fallback: store original
            return $file->storeAs($directory, $filename);
        }
    }
}
