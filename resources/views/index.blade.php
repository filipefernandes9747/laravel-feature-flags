<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feature Flags</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            width: 250px;
        }

        .add-btn {
            background: #6366f1;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .add-btn:hover {
            background: #5855eb;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }

        .feature-key {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }

        .feature-key:hover {
            text-decoration: underline;
        }

        .toggle {
            position: relative;
            width: 44px;
            height: 24px;
            background: #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .toggle.active {
            background: #6366f1;
        }

        .toggle::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.2s;
        }

        .toggle.active::after {
            transform: translateX(20px);
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status.on {
            background: #dcfce7;
            color: #166534;
        }

        .status.off {
            background: #fee2e2;
            color: #991b1b;
        }

        .timestamp {
            color: #666;
            font-size: 12px;
        }

        .override-tag {
            background: #f3f4f6;
            color: #6b7280;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }

        .value-cell {
            font-family: monospace;
            font-size: 13px;
            color: #374151;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Features</h1>
            <p>Features enable you to change your app's behavior from within the UI.</p>
        </div>

        <div class="controls">
            <input type="text" class="search-box" placeholder="Filter list..." id="searchInput">
            <button class="add-btn" onclick="addFeature()">+ Add Feature</button>
        </div>

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
                            @foreach ($environments as $environment)
                                <td>
                                    <div class="toggle {{ isFlagActive($flag, $environment) ? 'active' : '' }}"
                                        onclick="toggleFeature(this, '{{ isFlagActive($flag, $environment) }}', '{{ $environment }}')">
                                    </div>
                                </td>
                            @endforeach
                            <td class="timestamp"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleFeature(element, enabled, environment = null) {
            //element.classList.toggle('active');
            const formData = {
                ...(environment && {
                    environment: environment
                }),
                enabled: !(enabled === '1')
            }

            console.log(formData);

            /*
            fetch(`/flags/${flagKey}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        environment: environment,
                        enabled:
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Toggle response:', data);
                })
                .catch(error => {
                    console.error('Error toggling feature:', error);
                    // Optionally revert toggle on error:
                    element.classList.toggle('active');
                });
            */
        }

        function addFeature() {
            const name = prompt('Enter feature key name:');
            if (name) {
                const table = document.getElementById('featuresTable').getElementsByTagName('tbody')[0];
                const row = table.insertRow();

                row.innerHTML = `
                    <td><a href="#" class="feature-key">${name}</a></td>
                    <td><div class="toggle" onclick="toggleFeature(this)"></div></td>
                    <td><div class="toggle" onclick="toggleFeature(this)"></div></td>
                    <td><span class="status off">OFF</span></td>
                    <td></td>
                    <td class="timestamp">just now</td>
                `;
            }
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
</body>

</html>
