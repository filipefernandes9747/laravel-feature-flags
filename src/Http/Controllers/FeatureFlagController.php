<?php

namespace FilipeFernandes\FeatureFlags\Http\Controllers;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Http\Request;

class FeatureFlagController
{

    public function index()
    {
        $dbFlags = FeatureFlag::all()->keyBy('key');
        $definedFlags = config('feature-flags.flags', []);

        $flags = collect($definedFlags)->map(function ($_, $key) use ($dbFlags) {
            $record = $dbFlags->get($key);
            return [
                'key' => $key,
                'enabled' => $record?->enabled ?? false,
            ];
        });

        return view('feature-flags::index', compact('flags'));
    }

    public function toggle(Request $request, $key)
    {
        $flag = FeatureFlag::firstOrNew(['key' => $key]);
        $flag->enabled = !$flag->enabled;
        $flag->save();

        return redirect()->route('feature-flags.index');
    }
}
