<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('x-api-key');
        $signature = $request->header('x-signature');
        $timestamp = $request->header('x-timestamp');

        // Ganti dengan API key dan secret yang kamu simpan
        $validKey = '$2a$12$PEHz3fVnQPmNUn/4AMOMFOU9JWDhChwvHifJ40mdzVUpPXgfqtblm';
        $secret = '$2a$12$K0oahemjHPvbOfzOYyng7euHxNqSU6OiBsoCJpU0JOV7rPwxsQ.hq';

        // Validasi API key
        if ($apiKey !== $validKey) {
            return response()->json(['error' => 'Unauthorized (invalid key)'], 401);
        }

        // Cek expired
        // if (abs(time() - (int)$timestamp) > 300) { // 5 menit
        //     return response()->json(['error' => 'Request expired'], 403);
        // }

        // Buat signature ulang
        $dataToSign = $timestamp;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $secret);

        // if (!hash_equals($expectedSignature, $signature)) {
        //     return response()->json(['error' => 'Invalid signature'], 403);
        // }

        return $next($request);
    }
}
