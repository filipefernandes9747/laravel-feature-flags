@extends('feature-flags::layouts.app')

@section('title', 'Feature Flag Conditionals')

@section('description', $flag->description)
@section('extra')
    <div class="flag-key">{{ $flag->key }}</div>
    <div class="main-toggle">
        <div class="toggle active" onclick="toggleMainFlag(this)" title="Enable/Disable Flag"></div>
    </div>
@endsection


@push('styles')
@endpush

@section('content')
    @include('feature-flags::partials.header')
    <div class="environment-tabs">
        @foreach ($environments as $env)
            <button class="env-tab capitalize"
                onclick="switchEnvironment(this, '{{ $env }}')">{{ $env }}</button>
        @endforeach
    </div>


    <div class="rules-container">
        <div class="rules-header">
            <h2>Targeting Rules</h2>
            <p>Define conditions to control when this feature flag is enabled</p>
        </div>

        <!-- IF Condition -->
        <div class="condition-block" data-type="if">
            <div class="condition-header">
                <span class="condition-label">IF</span>
            </div>
            <div class="condition-row">
                <select class="form-select w-auto">
                    <option value="user">User</option>
                    <option value="session">Session</option>
                    <option value="request">Request</option>
                </select>
                <select class="form-select w-auto">
                    <option value="is-in-segment">is in segment</option>
                    <option value="equals">equals</option>
                    <option value="contains">contains</option>
                    <option value="starts-with">starts with</option>
                    <option value="ends-with">ends with</option>
                </select>
                <select class="form-select w-auto">
                    <option value="beta-testers">Beta Testers</option>
                    <option value="premium-users">Premium Users</option>
                    <option value="internal-team">Internal Team</option>
                    <option value="qa-team">QA Team</option>
                </select>
            </div>
        </div>

        <!-- THEN Condition -->
        <div class="condition-block" data-type="then">
            <div class="condition-header">
                <span class="condition-label then">THEN</span>
                <span>Serve:</span>
                <div class="toggle active" onclick="toggleCondition(this)"></div>
                <span>Enabled</span>
            </div>
        </div>

        <div class="logical-operator">OR</div>

        <!-- OTHERWISE Condition -->
        <div class="condition-block" data-type="otherwise">
            <button class="remove-btn" onclick="removeCondition(this)" title="Remove condition">×</button>
            <div class="condition-header">
                <span class="condition-label otherwise">OTHERWISE</span>
            </div>
            <div class="condition-row">
                <input type="number" class="form-input percentage-input" value="20" min="0" max="100">
                <span>% of</span>
                <select class="form-select w-auto">
                    <option value="users">users</option>
                    <option value="sessions">sessions</option>
                    <option value="requests">requests</option>
                </select>
                <span>→</span>
                <div class="toggle active" onclick="toggleCondition(this)"></div>
                <span>Enabled</span>
            </div>
            <div class="condition-row">
                <input type="number" class="form-input percentage-input" value="80" min="0" max="100">
                <span>% of</span>
                <select class="form-select w-auto">
                    <option value="users">users</option>
                    <option value="sessions">sessions</option>
                    <option value="requests">requests</option>
                </select>
                <span>→</span>
                <div class="toggle" onclick="toggleCondition(this)"></div>
                <span>Disabled</span>
            </div>
        </div>

        <!-- Default/Unidentified Condition -->
        <div class="condition-block" data-type="default">
            <div class="condition-header">
                <span class="condition-label otherwise">DEFAULT</span>
                <span>To unidentified users:</span>
                <div class="toggle" onclick="toggleCondition(this)"></div>
                <span>Disabled</span>
            </div>
        </div>

        <button class="add-condition-btn" onclick="addCondition()">
            + Add Targeting Rule
        </button>
    </div>

    <div class="actions">
        <button class="btn btn-primary" onclick="saveChanges()">Save & Publish Changes</button>
        <button class="btn btn-secondary" onclick="revertChanges()">Revert</button>
        <button class="btn btn-secondary" onclick="viewHistory()">View History</button>
    </div>
    </div>

    <script>
        let conditionCounter = 0;
        let currentEnvironment = 'prod';

        function toggleMainFlag(toggle) {
            toggle.classList.toggle('active');
            const isEnabled = toggle.classList.contains('active');
            console.log(`Main flag toggled: ${isEnabled}`);

            // Update all dependent rules based on main toggle
            updateRuleStates(isEnabled);
        }

        function toggleCondition(toggle) {
            toggle.classList.toggle('active');
            const isEnabled = toggle.classList.contains('active');
            const label = toggle.nextElementSibling;

            if (label && label.tagName === 'SPAN') {
                label.textContent = isEnabled ? 'Enabled' : 'Disabled';
            }

            console.log(`Condition toggled: ${isEnabled}`);
        }

        function switchEnvironment(tab, environment) {
            // Remove active class from all tabs
            document.querySelectorAll('.env-tab').forEach(t => t.classList.remove('active'));

            // Add active class to clicked tab
            tab.classList.add('active');
            currentEnvironment = environment;

            console.log(`Switched to environment: ${environment}`);

            // Here you would load the rules for the selected environment
            loadEnvironmentRules(environment);
        }

        function loadEnvironmentRules(environment) {
            // Simulate loading different rules for different environments
            console.log(`Loading rules for ${environment} environment`);

            // You could modify the UI here to show environment-specific rules
            const header = document.querySelector('.header h1');
            const originalTitle = 'Enable New Design';
            header.textContent = `${originalTitle} (${environment.toUpperCase()})`;
        }

        function addCondition() {
            const rulesContainer = document.querySelector('.rules-container');
            const addButton = document.querySelector('.add-condition-btn');

            conditionCounter++;
            const newCondition = document.createElement('div');
            newCondition.className = 'condition-block';
            newCondition.dataset.type = 'custom';

            newCondition.innerHTML = `
                <button class="remove-btn" onclick="removeCondition(this)" title="Remove condition">×</button>
                <div class="condition-header">
                    <span class="condition-label">AND IF</span>
                </div>
                <div class="condition-row">
                    <select class="form-select">
                        <option value="user">User</option>
                        <option value="session">Session</option>
                        <option value="request">Request</option>
                    </select>
                    <select class="form-select">
                        <option value="equals">equals</option>
                        <option value="contains">contains</option>
                        <option value="is-in-segment">is in segment</option>
                        <option value="starts-with">starts with</option>
                        <option value="ends-with">ends with</option>
                    </select>
                    <input type="text" class="form-input" placeholder="Enter value" style="min-width: 150px;">
                    <span>→</span>
                    <div class="toggle" onclick="toggleCondition(this)"></div>
                    <span>Disabled</span>
                </div>
            `;

            rulesContainer.insertBefore(newCondition, addButton);
            console.log('Added new targeting rule');
        }

        function removeCondition(button) {
            const condition = button.closest('.condition-block');
            const conditionType = condition.dataset.type;

            // Don't allow removal of core conditions
            if (conditionType !== 'if' && conditionType !== 'then' && conditionType !== 'default') {
                condition.remove();
                console.log('Removed targeting rule');
            } else {
                alert('Cannot remove core targeting rules');
            }
        }

        function updateRuleStates(mainFlagEnabled) {
            const toggles = document.querySelectorAll('.condition-block .toggle');

            if (!mainFlagEnabled) {
                // If main flag is disabled, show visual indication
                document.querySelector('.rules-container').style.opacity = '0.6';
                document.querySelector('.rules-container').style.pointerEvents = 'none';
            } else {
                document.querySelector('.rules-container').style.opacity = '1';
                document.querySelector('.rules-container').style.pointerEvents = 'auto';
            }
        }

        function saveChanges() {
            // Collect all targeting rules
            const rules = [];
            const conditions = document.querySelectorAll('.condition-block');

            conditions.forEach((condition, index) => {
                const type = condition.dataset.type;
                const toggles = condition.querySelectorAll('.toggle');
                const selects = condition.querySelectorAll('.form-select');
                const inputs = condition.querySelectorAll('.form-input');

                const rule = {
                    id: index,
                    type: type,
                    environment: currentEnvironment,
                    conditions: Array.from(selects).map(s => s.value),
                    values: Array.from(inputs).map(i => i.value),
                    enabled: toggles.length > 0 ? Array.from(toggles).map(t => t.classList.contains('active')) :
                        []
                };

                rules.push(rule);
            });

            console.log('Saving targeting rules:', {
                flagKey: 'enableNewDesign',
                environment: currentEnvironment,
                mainToggle: document.querySelector('.main-toggle .toggle').classList.contains('active'),
                rules: rules
            });

            // Show success message
            showNotification('Changes saved and published successfully!', 'success');
        }

        function revertChanges() {
            if (confirm('Are you sure you want to revert all unsaved changes?')) {
                location.reload();
            }
        }

        function viewHistory() {
            console.log('Opening history view');
            showNotification('Opening change history...', 'info');
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'};
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                z-index: 1000;
                font-weight: 500;
                animation: slideIn 0.3s ease;
            `;

            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Validate percentage inputs
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('percentage-input')) {
                const value = parseInt(e.target.value);
                if (value < 0) e.target.value = 0;
                if (value > 100) e.target.value = 100;
            }
        });

        // Tab switching
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                document.querySelectorAll('nav a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                console.log(`Switched to tab: ${this.textContent}`);
            });
        });
    </script>
@endsection
