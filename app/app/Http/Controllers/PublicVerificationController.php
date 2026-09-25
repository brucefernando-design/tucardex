<?php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    /**
     * Validador público de autenticidad de documentos TuCardex
     */
    public function show(string $token): View
    {
        $verification = DocumentVerification::where('token', $token)->first();

        return view('public.verification', [
            'found' => (bool) $verification,
            'verification' => $verification,
        ]);
    }
}
