<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display all settings grouped by their 'group' column.
     */
    public function index()
    {
        $settings      = Setting::all()->groupBy('group');
        $settingValues = Setting::pluck('value', 'key');
        return view('settings.index', compact('settings', 'settingValues'));
    }

    /**
     * Persist updated settings.
     * Iterates all request fields (except _token and _method) and calls Setting::set().
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->back()->with('success', 'Settings saved!');
    }

    /**
     * Handle business logo upload.
     * Stores the file in public/logos and saves the path under the key 'business_logo'.
     */
    public function storeLogo(Request $request)
    {
        $request->validate([
            'business_logo' => 'required|image|max:2048',
        ]);

        $path = $request->file('business_logo')->store('logos', 'public');

        Setting::set('business_logo', $path);

        return redirect()->back()->with('success', 'Logo uploaded successfully!');
    }

    /**
     * Default application settings for initial seeding.
     *
     * @return array<string, string>
     */
    public static function defaultSettings(): array
    {
        return [
            'business_name'            => 'Mayank Footware',
            'business_address'         => 'Shop No. 12, Main Market, City',
            'business_phone'           => '98765-43210',
            'business_gst'             => '24XXXXX1234X1Z5',
            'currency_symbol'          => '₹',
            'tax_percent'              => '0',
            'receipt_footer'           => 'Thank You! Come Again!',
            'receipt_exchange_policy'  => 'Exchange within 7 days (with bill only)',
            'low_stock_alert_qty'      => '5',
            'barcode_label_width'      => '60',
            'barcode_label_height'     => '30',
        ];
    }
}
