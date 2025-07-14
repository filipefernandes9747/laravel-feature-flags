<?php

namespace FilipeFernandes\FeatureFlags\Http\Controllers;

use FilipeFernandes\FeatureFlags\Actions\CreateFlag;
use FilipeFernandes\FeatureFlags\Actions\ToggleFlag;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeatureFlagController
{

    public function index()
    {
        $environments = config('feature-flags.environments', []);

        $routeEndpoint = config('feature-flags.ui.route_prefix', 'admin/flags');

        $flags = $this->getAllFlags($environments);

        return view('feature-flags::index', [
            'flags' => $flags,
            'environments' => $environments,
            'route' => $routeEndpoint
        ]);
    }

    private function getAllFlags(array $environments)
    {
        // Get all DB flags keyed by 'key'
        $dbFlags = FeatureFlag::all()->keyBy('key');

        // Start with DB flags mapped to array format
        $flags = $dbFlags->map(function ($flag) use ($environments) {
            return [
                'key' => $flag->key,
                'enabled' => !empty($environments) && !empty($flag->environments) ? $flag->environments : $flag->enabled,
                'updated_at' => $flag->updated_at
            ];
        });

        // Load config flags
        $configFlags = config('feature-flags.flags', []);

        // Add config flags only if not in DB
        foreach ($configFlags as $key => $configFlag) {
            if (!$dbFlags->has($key)) {
                $flags->put($key, [
                    'key' => $key,
                    'enabled' => $configFlag['enabled'],
                    'updated_at' => null
                ]);
            }
        }

        // Return indexed array
        return $flags->values()->toArray();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'environment' => ['sometimes', 'string', 'in:' . implode(',', config('feature-flags.environments', []))],
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $flag = (new CreateFlag)->handle($validator->validated());

            if (!$flag) {
                return response()->json(['message' => 'Failed to create feature flag.'], 500);
            }

            return response()->json([
                'message' => 'Feature flag created successfully.',
                'flag' => $flag
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred while creating the feature flag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, $key)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'environment' => ['sometimes', 'string', 'in:' . implode(',', config('feature-flags.environments', []))],
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();

        $data['key'] = $key;

        if (!(new ToggleFlag)->handle($data)) {
            return response()->json(['message' => 'Failed to save feature flag.'], 500);
        }
        return response()->json(['message' => 'Successfull save feature flag.'], 200);
    }
}
