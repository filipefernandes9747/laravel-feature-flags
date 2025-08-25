<?php

namespace FilipeFernandes\FeatureFlags\Http\Controllers;

use FilipeFernandes\FeatureFlags\Actions\CreateFlag;
use FilipeFernandes\FeatureFlags\Actions\DeleteFlag;
use FilipeFernandes\FeatureFlags\Actions\ToggleFlag;
use FilipeFernandes\FeatureFlags\FeatureFlags;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use FilipeFernandes\FeatureFlags\Models\FeatureFlagHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeatureFlagController
{
    public function __construct(protected FeatureFlags $service) {}


    public function index()
    {
        $environments = config('feature-flags.environments', []);

        $routeEndpoint = config('feature-flags.ui.route_prefix', 'admin/flags');

        $flags = $this->service->getAllFlags($environments);

        return view('feature-flags::index', [
            'flags' => $flags,
            'environments' => $environments,
            'route' => $routeEndpoint,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'environment' => ['sometimes', 'string', 'in:' . implode(',', config('feature-flags.environments', []))],
            'metadata' => 'nullable|array',
            'description' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $flag = (new CreateFlag)->handle($validator->validated());

            return response()->json([
                'message' => 'Feature flag created successfully.',
                'flag' => $flag,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred while creating the feature flag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, string $key)
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

        if (! (new ToggleFlag)->handle($data)) {
            return response()->json(['message' => 'Failed to save feature flag.'], 500);
        }

        return response()->json(['message' => 'Successfull save feature flag.'], 200);
    }

    public function delete(string $flag)
    {
        if (! (new DeleteFlag)->handle($flag)) {
            return response()->json(['message' => 'Failed to delete feature flag.'], 500);
        }

        return response()->json(['message' => 'Successfull delete feature flag.'], 200);
    }

    public function indexHistory(Request $request)
    {
        $query = FeatureFlagHistory::orderBy('created_at', 'desc');

        if ($request->has('filter') && $request->filter) {
            $query->where('key', 'like', '%' . $request->filter . '%')
                ->orWhere('changed_by', 'like', '%' . $request->filter . '%')
                ->orWhere('event', 'like', '%' . $request->filter . '%');
        }

        $histories = $query->paginate(15);

        return view('feature-flags::history', [
            'histories' => $histories,
        ]);
    }

    public function showConditionals(string $flag, Request $request)
    {
        $environments = config('feature-flags.environments', []);

        $flag = FeatureFlag::where('key', $flag)->firstOrFail();

        return view('feature-flags::conditionals', [
            'flag' => $flag,
            'environments' => $environments,
        ]);
    }
}
