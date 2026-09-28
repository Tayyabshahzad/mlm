<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    // ── User: show upload form ────────────────────────────────────────────────

    public function show()
    {
        $user = Auth::user();

        if (!in_array($user->kyc_status, ['pending', 'submitted', 'rejected'])) {
            return redirect()->route('dashboard')->with('info', 'No KYC verification is required for your account.');
        }

        $frontUrl = $user->getFirstMediaUrl('kyc_cnic_front');
        $backUrl  = $user->getFirstMediaUrl('kyc_cnic_back');

        return view('kyc.upload', compact('user', 'frontUrl', 'backUrl'));
    }

    // ── User: submit documents ────────────────────────────────────────────────

    public function submit(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!in_array($user->kyc_status, ['pending', 'rejected'])) {
            return redirect()->route('dashboard')->with('error', 'KYC submission is not currently open for your account.');
        }

        $request->validate([
            'cnic_front' => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'cnic_back'  => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $user->addMedia($request->file('cnic_front'))->toMediaCollection('kyc_cnic_front');
        $user->addMedia($request->file('cnic_back'))->toMediaCollection('kyc_cnic_back');

        $user->update([
            'kyc_status'       => 'submitted',
            'kyc_submitted_at' => now(),
            'kyc_rejection_reason' => null,
        ]);

        return redirect()->route('kyc.show')->with('success', 'KYC documents submitted successfully. Admin will review shortly.');
    }

    // ── Admin: request KYC from a user ───────────────────────────────────────

    public function adminRequest(User $user): RedirectResponse
    {
        if (in_array($user->kyc_status, ['approved'])) {
            return back()->with('error', 'KYC is already approved for this user.');
        }

        $user->update(['kyc_status' => 'pending']);

        return back()->with('success', "KYC verification request sent to {$user->name}.");
    }

    // ── Admin: approve KYC ───────────────────────────────────────────────────

    public function adminApprove(User $user): RedirectResponse
    {
        if ($user->kyc_status !== 'submitted') {
            return back()->with('error', 'No submitted KYC documents to approve.');
        }

        $user->update([
            'kyc_status'       => 'approved',
            'kyc_reviewed_at'  => now(),
            'kyc_reviewed_by'  => Auth::id(),
            'kyc_rejection_reason' => null,
        ]);

        return back()->with('success', "KYC approved for {$user->name}.");
    }

    // ── Admin: reject KYC ────────────────────────────────────────────────────

    public function adminReject(Request $request, User $user): RedirectResponse
    {
        if ($user->kyc_status !== 'submitted') {
            return back()->with('error', 'No submitted KYC documents to reject.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        // Clear uploaded images so user must re-upload
        $user->clearMediaCollection('kyc_cnic_front');
        $user->clearMediaCollection('kyc_cnic_back');

        $user->update([
            'kyc_status'           => 'rejected',
            'kyc_reviewed_at'      => now(),
            'kyc_reviewed_by'      => Auth::id(),
            'kyc_rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', "KYC rejected for {$user->name}. User will be notified to re-upload.");
    }
}
