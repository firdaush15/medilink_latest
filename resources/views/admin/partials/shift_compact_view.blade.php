{{-- resources/views/admin/partials/shift_compact_view.blade.php --}}

<div class="compact-view">
    @foreach($staff as $person)
    @php
        $stats = $staffStats[$person->id] ?? [
            'total_shifts' => 0,
            'total_hours' => 0,
            'checked_in' => 0,
            'scheduled' => 0,
            'absent' => 0
        ];
    @endphp
    
    <div class="compact-staff-card {{ $person->role }}" data-staff-id="{{ $person->id }}">
        <div class="compact-header" onclick="toggleCompactExpand({{ $person->id }})">
            <div class="compact-staff-info">
                <div class="compact-avatar">
                    {{ strtoupper(substr($person->name, 0, 2)) }}
                </div>
                <div class="compact-details">
                    <h4>{{ $person->name }}</h4>
                    <div class="role-label">{{ ucfirst($person->role) }}</div>
                </div>
            </div>

            <div class="compact-stats">
                <div class="compact-stat-item">
                    <div class="compact-stat-value">{{ $stats['total_shifts'] }}</div>
                    <div class="compact-stat-label">Shifts</div>
                </div>
                <div class="compact-stat-item">
                    <div class="compact-stat-value">{{ $stats['total_hours'] }}h</div>
                    <div class="compact-stat-label">Hours</div>
                </div>
                <div class="compact-stat-item">
                    <div class="compact-stat-value">{{ $stats['checked_in'] }}/{{ $stats['scheduled'] }}</div>
                    <div class="compact-stat-label">Present</div>
                </div>
                <div class="expand-icon" id="expand-icon-{{ $person->id }}">
                    ▼
                </div>
            </div>
        </div>

        <div class="compact-shifts-detail" id="detail-{{ $person->id }}">
            @for($i = 0; $i < 7; $i++)
                @php
                    $day = $weekStart->copy()->addDays($i);
                    $dayShifts = $shifts->filter(function($shift) use ($person, $day) {
                        return $shift->user_id == $person->id && 
                               $shift->shift_date->isSameDay($day);
                    });
                @endphp

                <div class="compact-day-column">
                    <div class="compact-day-header">
                        {{ $day->format('D') }}<br>
                        <span style="font-size: 10px;">{{ $day->format('M d') }}</span>
                    </div>
                    
                    @forelse($dayShifts as $shift)
                        <div class="compact-shift-badge" 
                             onclick="editShift({{ $shift->shift_id }})"
                             title="{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}">
                            {{ $shift->start_time->format('H:i') }}
                        </div>
                    @empty
                        <div class="compact-empty" 
                             onclick="addShift({{ $person->id }}, '{{ $person->name }}', '{{ $person->role }}', '{{ $day->format('Y-m-d') }}')"
                             title="Add shift">
                            +
                        </div>
                    @endforelse
                </div>
            @endfor
        </div>
    </div>
    @endforeach
</div>