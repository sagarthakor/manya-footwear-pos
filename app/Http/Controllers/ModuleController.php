<?php

namespace App\Http\Controllers;

use App\Helpers\Module;
use App\Models\Setting;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $definitions = Module::definitions();
        $status = [];
        foreach ($definitions as $key => $info) {
            $status[$key] = Module::isActive($key);
        }
        return view('modules.index', compact('definitions', 'status'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['module' => 'required|string|in:' . implode(',', array_keys(Module::definitions()))]);

        $key     = 'module_' . $request->module;
        $current = Setting::get($key, '1');
        $newVal  = $current === '1' ? '0' : '1';

        Setting::set($key, $newVal, 'modules');
        Module::clearCache();

        return response()->json(['active' => $newVal === '1']);
    }
}