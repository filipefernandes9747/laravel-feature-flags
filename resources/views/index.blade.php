@extends('feature-flags::layouts.app')

@section('title', 'Features')

@section('description', "Features enable you to change your app's behavior from within the UI.")


@push('styles')
    <style>
        .welcome {
            color: green;
        }
    </style>
@endpush

@section('content')
    @include('feature-flags::partials.header')
    @include('feature-flags::partials.controls', [
        'showAdd' => true,
    ])

    <div class="table-container">
        <table id="featuresTable">
            <thead>
                <tr>
                    <th>Feature Key</th>
                    @foreach ($environments as $environment)
                        <th>{{ $environment }}</th>
                    @endforeach
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flags as $flag)
                    <tr>
                        <td><a href="#" class="feature-key">{{ $flag['key'] }}</a>
                        </td>
                        @if (!empty($environments))
                            @foreach ($environments as $environment)
                                <td>
                                    <div class="toggle {{ isFlagActive($flag, $environment) ? 'active' : '' }}"
                                        onclick="toggleFeature(this, '{{ $flag['key'] }}', '{{ isFlagActive($flag, $environment) }}', '{{ $environment }}')">
                                    </div>
                                </td>
                            @endforeach
                        @else
                            <td>
                                <div class="toggle {{ isFlagActive($flag) ? 'active' : '' }}"
                                    onclick="toggleFeature(this, '{{ $flag['key'] }}', '{{ isFlagActive($flag) }}')">
                                </div>
                            </td>
                        @endif
                        <td class="timestamp">{{ \Carbon\Carbon::parse($flag['updated_at'])->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleFeature(element, key, enabled, environment = null) {
            //
            const formData = {
                ...(environment && {
                    environment: environment
                }),
                enabled: !(enabled === '1')
            }
            const route = '{{ $route }}';

            fetch(`/${route}/${key}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(formData),
                })
                .then(response => response.json())
                .then(data => {
                    element.classList.toggle('active');
                })
                .catch(error => {
                    console.error('Error toggling feature:', error);
                });
        }

        function addFeature() {
            const name = prompt('Enter feature key name:');

            if (!name) {
                return;
            }

            const formData = {
                name: name
            };

            const route = '{{ $route }}';
            const environments = @json($environments);


            fetch(`/${route}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(formData),
                })
                .then(response => response.json())
                .then(data => {
                    const table = document.getElementById('featuresTable').getElementsByTagName('tbody')[0];
                    const row = table.insertRow();
                    const environments = @json($environments);


                    // Build cells with JavaScript only
                    let html = `<td><a href="#" class="feature-key">${data.flag.key}</a></td>`;

                    for (const environment of environments) {
                        const isActive = data.flag.environments.includes(environment);
                        html += `
                            <td>
                                <div class="toggle ${isActive ? 'active' : ''}"
                                    onclick="toggleFeature(this, '${data.flag.key}', '${isActive}', '${environment}')">
                                </div>
                            </td>
                        `;
                    }

                    html += `<td class="timestamp">just now</td>`;
                    row.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error toggling feature:', error);
                });

        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#featuresTable tbody tr');

            rows.forEach(row => {
                const featureKey = row.querySelector('.feature-key').textContent.toLowerCase();
                if (featureKey.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endpush
