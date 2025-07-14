<?php

namespace FilipeFernandes\FeatureFlags\Http\Controllers;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
                'enabled' => $record->enabled ?? $_['enabled'],
            ];
        });

        $environments = config('feature-flags.environments', []);

        return view('feature-flags::index', [
            'flags' => $flags,
            'environments' => $environments
        ]);
    }

    public function toggle(Request $request, $key)
    {
        $flag = FeatureFlag::firstOrNew(['key' => $key]);

        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'environment' => ['sometimes', 'string', 'in:' . implode(',', config('feature-flags.environments', []))],
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $environment = $data['environment'] ?? null;
        $enabled = $data['enabled'];
        $metadata = $data['metadata'] ?? [];

        if ($environment) {
            $environments = $flag->environments ?? [];
            $environments[$environment] = $enabled;
            $flag->environments = $environments;
            $flag->enabled = collect($environments)->contains(true);
        } else {
            $flag->enabled = $enabled;
        }

        $flag->metadata = $metadata;
        $flag->save();

        return redirect()->route('feature-flags.index');
    }
}
