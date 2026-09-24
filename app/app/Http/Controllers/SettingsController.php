<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->get();
        $setting = Setting::current();

        return view('settings.index', compact('roles', 'setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'academic_year' => ['required', 'string', 'max:10'],
            'active_period' => ['required', 'in:1er Trimestre,2do Trimestre,3er Trimestre,Final'],
            'currency' => ['required', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'director'            => ['nullable', 'string', 'max:150'],
            'cedula_profesional'  => ['nullable', 'string', 'max:20'],
            'tuition_amount' => ['required', 'numeric', 'min:0'],
            'platform_fee_enabled' => ['nullable', 'boolean'],
            'platform_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'platform_fee_label' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $setting = Setting::current();

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($data['logo']);
        }

        $data['platform_fee_enabled'] = $request->boolean('platform_fee_enabled');
        if ($request->filled('platform_fee_amount')) {
            $data['platform_fee_amount'] = (float) $request->input('platform_fee_amount');
        }
        if ($request->filled('platform_fee_label')) {
            $data['platform_fee_label'] = trim($request->input('platform_fee_label'));
        }
        $setting->update($data);

        return redirect()->route('settings.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
