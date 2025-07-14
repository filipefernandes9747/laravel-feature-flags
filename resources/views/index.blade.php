@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-xl font-bold mb-4">🔀 Feature Flags</h1>

        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 border-b">Flag</th>
                    <th class="px-4 py-2 border-b">Enabled</th>
                    <th class="px-4 py-2 border-b">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flags as $flag)
                    <tr>
                        <td class="border px-4 py-2">{{ $flag['key'] }}</td>
                        <td class="border px-4 py-2">
                            @if ($flag['enabled'])
                                ✅
                            @else
                                ❌
                            @endif
                        </td>
                        <td class="border px-4 py-2">
                            <form action="{{ route('feature-flags.toggle', $flag['key']) }}" method="POST">
                                @csrf
                                <button class="px-3 py-1 bg-indigo-500 text-white rounded hover:bg-indigo-600">
                                    Toggle
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
