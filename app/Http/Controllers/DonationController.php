<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    public function index()
    {
        $donations = Donation::orderBy('created_at', 'desc')->take(10)->get();
        return response()->json($donations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1000',
            'message' => 'nullable|string|max:500',
        ]);

        $merchantCode = 'WP6975ae5bee603';
        $apiKey = '8a68990902bc4536f7fb8b5891';
        $refId = 'TRK-' . time() . '-' . Str::random(5);

        $signature = md5($merchantCode . $apiKey . $refId);

        try {
            $response = Http::withHeaders([
                'X-Signature' => $signature,
            ])->post('https://wijayapay.com/api/transaction/create', [
                'code_merchant' => $merchantCode,
                'api_key' => $apiKey,
                'code_payment' => 'QRIS',
                'ref_id' => $refId,
                'nominal' => (int) $request->nominal,
                'note' => 'Traktir Admin', // Optional, good for context
            ]);

            //             $exampleJson = [
            //                 'success' => true,
            //                 'data' => [
            //                     'payment_name' => 'QRIS',
            //                     'payment_method' => 'QRIS',
            //                     'total_bayar' => 5000,
            //                     'total_fee' => 135,
            //                     'total_diterima' => 4865,
            //                     'ref_id' => 'TRK-1769322178-ydMFO',
            //                     'trx_reference' => 'WPTRX840597',
            //                     'expired' => '2026-01-25 14:22:59',
            //                     'tutorial_pembayaran' => "Buka aplikasi yang mendukung pembayaran dengan QRIS
            // Pilih fitur QRIS / Bayar
            // Pindai kode QR yang diberikan oleh Merchant
            // Pastikan tagihan yang ditagihkan sesuai
            // Klik tombol Konfirmasi
            // Masukkan PIN untuk menyelesaikan pembayaran
            // Setelah pembayaran berhasil, kamu akan dialihkan ke Halaman Hasil Pembayaran",
            //                     'qr_image' => 'https://wijayapay.com/qris/6975b6c37f298.png',
            //                     'qr_string' => '00020101021226640017ID.CO.DANAMON.WWW0118936000110000307977021010317974020303UBE51440014ID.CO.QRIS.WWW0215ID20254627467070303UBE6271011617693225177749770511WPTRX8405970709221252420991900020001094144360195303360540450006006BEKASI520465335910Wijaya Pay5802ID61051713563047BDC'
            //                 ]
            //             ];

            $responseData = $response->json();

            // Check if request was successful based on WijayaPay response structure
            // Assuming 'success' is true or similar. If not robust, we just assume data presence or specific status.
            // Adjust based on real API response if known. For now, we save raw response or specific field.

            // Assuming response contains 'qr_string' or similar for QRIS content.
            // Since we don't have the exact response doc, we'll store the entire 'qr_link' or 'qr_content' if exists,
            // or simply the whole response body to 'qris_content' for debugging if structure is unknown.
            // Based on typical QRIS APIs, it might return a 'qr_string' or 'qr_image'.

            // Let's assume the 'qr_string' or 'checkout_url' is what we need. 
            // The prompt says "generate qris", so likely we get a QR string (payload).

            $qrisContent = $responseData['data']['qr_string'] ?? $responseData['data']['qr_image'] ?? json_encode($responseData);

            $donation = Donation::create([
                'ref_id' => $refId,
                'nominal' => $request->nominal,
                'message' => $request->message,
                'status' => 'pending',
                'qris_content' => $qrisContent,
            ]);

            return response()->json([
                'message' => 'Donation created successfully',
                'donation' => $donation,
                'payment_response' => $responseData, // Return full response for frontend to parse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function checkStatus($refId)
    {
        $donation = Donation::where('ref_id', $refId)->firstOrFail();

        $merchantCode = 'WP6975ae5bee603';
        $apiKey = '8a68990902bc4536f7fb8b5891';

        try {
            // GET request as per user example
            $response = Http::get('https://wijayapay.com/api/get-status', [
                'code_merchant' => $merchantCode,
                'api_key' => $apiKey,
                'ref_id' => $refId,
            ]);

            $data = $response->json();

            // Log response for debugging if needed
            // \Log::info('WijayaPay Check Status:', $data);

            if (isset($data['status_pembayaran'])) {
                $statusPembayaran = strtoupper($data['status_pembayaran']);

                // Map status
                $newStatus = 'pending';
                if ($statusPembayaran === 'PAID' || $statusPembayaran === 'SUCCESS' || $statusPembayaran === 'BERHASIL') {
                    $newStatus = 'paid';
                } elseif ($statusPembayaran === 'EXPIRED' || $statusPembayaran === 'FAILED' || $statusPembayaran === 'GAGAL') {
                    $newStatus = 'failed';
                }

                // Update if changed
                if ($donation->status !== $newStatus) {
                    $donation->update(['status' => $newStatus]);
                }

                return response()->json([
                    'message' => 'Status updated',
                    'status' => $newStatus,
                    'original_status' => $data['status_pembayaran'],
                    'data' => $data
                ]);
            }

            return response()->json([
                'message' => 'Status field not found in response',
                'data' => $data
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
