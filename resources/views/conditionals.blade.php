@extends('feature-flags::layouts.app')

@section('title', 'Feature Flag Conditionals')

@section('description', $flag->description)
@section('extra')
    <div class="flag-key">{{ $flag->key }}</div>
    <div class="main-toggle">
        <small>Enable/Disable globally</small>
        <div class="toggle {{ $flag->enabled ? 'active' : '' }}" title="Enable/Disable Flag"></div>
    </div>
@endsection


@push('styles')
@endpush

@section('content')
    @include('feature-flags::partials.header')

    <form id="rules">
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
                <div id="and_contition_list">
                    @if (empty($flag->conditions?->and))
                        <div class="condition-row position-relative">
                            @include('feature-flags::partials.conditional-form', [
                                'condition' => null,
                                'removable' => false,
                            ])
                        </div>
                    @else
                        @foreach ($flag->conditions->and as $condition)
                            <div class="condition-row position-relative">
                                @include('feature-flags::partials.conditional-form', [
                                    'condition' => $condition,
                                    'removable' => true,
                                ])
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Hidden template for JS --}}
            <template id="condition-template">
                <div class="position-relative condition-row">
                    @include('feature-flags::partials.conditional-form', [
                        'condition' => null,
                        'removable' => true,
                    ])
                </div>
            </template>
            <button id="add_condition_and" class="add-condition-btn">
                + Add Targeting Rule
            </button>
        </div>

        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                ← Back
            </button>
            <button type="submit" class="btn btn-primary">Save & Publish Changes</button>
        </div>
    </form>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="myToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Hello, this is a toast notification!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
    </div>

    <script>
        $(function() {
            // Toggle main flag
            $('.main-toggle .toggle').click(function() {
                $(this).toggleClass('active');
                updateRuleStates($(this).hasClass('active'));
            });

            // Add new condition
            $('#add_condition_and').click(function() {
                const $template = $($('#condition-template').html());
                $('#and_contition_list').append($template);
            });


            // Remove condition
            $(document).on('click', '.remove-btn', function() {
                const $condition = $(this).closest('.condition-block, .condition-row');
                const type = $condition.data('type');
                if (type !== 'if' && type !== 'then' && type !== 'default') {
                    $condition.remove();
                } else {
                    alert('Cannot remove core targeting rules');
                }
            });

            $(document).on('change', 'select[name="operation"]', function() {
                if ($(this).val() !== 'in') {
                    $(this).parent().find('.value-key').removeClass('d-none').attr('required', true);
                    $(this).parent().find('.value-input').removeClass('d-none').attr('required', true);
                    $(this).parent().find('.value-select').addClass('d-none').attr('required', false);
                } else {
                    $(this).parent().find('.value-key').addClass('d-none').attr('required', false);
                    $(this).parent().find('.value-input').addClass('d-none').attr('required', false);
                    $(this).parent().find('.value-select').removeClass('d-none').attr('required', true);
                }
            });

            $('#rules').submit(function(e) {
                e.preventDefault();

                const formData = {
                    conditions: {
                        and: getConditions('and')
                    }
                };

                const route = '{{ $route }}';
                const flagKey = '{{ $flag->key }}';
                const endpoint = `/${route}/${flagKey}/conditionals`;

                const toastEl = $("#myToast");
                const toastBody = toastEl.find(".toast-body"); // find message container
                toastEl.removeClass('text-bg-primary text-bg-danger text-bg-success');

                const toast = new bootstrap.Toast(toastEl[0], {
                    delay: 3000
                });

                fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(formData),
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (response.ok) {
                            toastEl.addClass('text-bg-success');
                            toastBody.text(data.message || 'Conditions updated successfully!');
                        } else {
                            toastEl.addClass('text-bg-danger');
                            toastBody.text(data.message || 'Failed to update conditions.');
                        }
                    })
                    .catch(error => {
                        toastEl.addClass('text-bg-danger');
                        toastBody.text('Network error while saving conditions.');
                        console.error('Error toggling feature:', error);
                    })
                    .finally(() => {
                        toast.show();
                    });
            });


            function getConditions(type) {
                var conditions = [];

                $(`#${type}_contition_list .condition-row`).each(function() {
                    var $row = $(this);
                    const condition = {
                        context: $row.find('select[name="context"]').val(),
                        operation: $row.find('select[name="operation"]').val()
                    };

                    if (condition.operation !== 'in') {
                        condition.key = $row.find('.value-key').val();
                        condition.value = $row.find('.value-input').val();
                    } else {
                        condition.value = $row.find('.value-select').val()
                    }

                    conditions.push(condition);
                });

                return conditions;
            }


            function loadEnvironmentRules(env) {
                const $header = $('.header h1');
                const originalTitle = 'Feature Flag Conditionals';
                $header.text(`${originalTitle} (${env.toUpperCase()})`);
            }

            function updateRuleStates(mainFlagEnabled) {
                $('.rules-container').css({
                    'opacity': mainFlagEnabled ? '1' : '0.6',
                    'pointer-events': mainFlagEnabled ? 'auto' : 'none'
                });

                const route = '{{ $route }}';
                const flagKey = '{{ $flag->key }}';
                const endpoint = `/${route}/${flagKey}/toggle`;

                const toastEl = $("#myToast");
                const toastBody = toastEl.find(".toast-body"); // find message container
                toastEl.removeClass('text-bg-primary text-bg-danger text-bg-success');

                const formData = {
                    enabled: mainFlagEnabled
                };

                const toast = new bootstrap.Toast(toastEl[0], {
                    delay: 3000
                });

                fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(formData),
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (response.ok) {
                            toastEl.addClass('text-bg-success');
                            toastBody.text(data.message || 'Updated successfully!');
                        } else {
                            toastEl.addClass('text-bg-danger');
                            toastBody.text(data.message || 'Failed to update.');
                        }
                    })
                    .catch(error => {
                        toastEl.addClass('text-bg-danger');
                        toastBody.text('Network error while saving.');
                        console.error('Error toggling feature:', error);
                    })
                    .finally(() => {
                        toast.show();
                    });
            }

        });
    </script>
@endsection
