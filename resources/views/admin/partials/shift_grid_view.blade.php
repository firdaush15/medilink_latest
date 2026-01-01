{{-- resources/views/admin/partials/shift_grid_view.blade.php --}}

<div class="calendar-grid">
    {{-- Header Row --}}
    <div class="calendar-header-cell staff-header">
        <strong>STAFF MEMBER</strong>
    </div>
    @for($i = 0; $i < 7; $i++)
        @php
            $day = $weekStart->copy()->addDays($i);
            $isToday = $day->isToday();
        @endphp
        <div class="calendar-header-cell {{ $isToday ? 'today' : '' }}">
            {{ $day->format('D') }}
            <small>{{ $day->format('M d') }}</small>
            @if($isToday)
                <small style="display: block; margin-top: 4px;">TODAY</small>
            @endif
        </div>
    @endfor

    {{-- Staff Rows --}}
    @foreach($staff as $person)
    <div class="staff-row">
        <div class="staff-name-cell {{ $person->role }}">
            <div class="name">{{ $person->name }}</div>
            <div class="role">{{ ucfirst($person->role) }}</div>
        </div>

        @for($i = 0; $i < 7; $i++)
            @php
                $day = $weekStart->copy()->addDays($i);
                $isToday = $day->isToday();
                $dayShifts = $shifts->filter(function($shift) use ($person, $day) {
                    return $shift->user_id == $person->id && 
                           $shift->shift_date->isSameDay($day);
                });
            @endphp

            <div class="shift-cell {{ $isToday ? 'today' : '' }}" 
                 onclick="addShift({{ $person->id }}, '{{ $person->name }}', '{{ $person->role }}', '{{ $day->format('Y-m-d') }}')">
                
                @forelse($dayShifts as $shift)
                    <div class="shift-item"
                         onclick="event.stopPropagation(); editShift({{ $shift->shift_id }})">
                        <div class="shift-time">
                            ⏰ {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                        </div>
                        <div style="font-size: 11px; opacity: 0.9;">
                            {{ $shift->template->template_name ?? 'Custom' }}
                        </div>
                        <div class="shift-actions">
                            <button onclick="event.stopPropagation(); editShift({{ $shift->shift_id }})">✏️ Edit</button>
                            <button onclick="event.stopPropagation(); deleteShift({{ $shift->shift_id }})">🗑️</button>
                        </div>
                    </div>
                @empty
                    <div class="empty-cell-hint">+ Add Shift</div>
                @endforelse
            </div>
        @endfor
    </div>
    @endforeach
</div>