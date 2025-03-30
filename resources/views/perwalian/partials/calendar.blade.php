<table class="calendar-table">
    <thead>
        <tr>
            <th>Sun</th>
            <th>Mon</th>
            <th>Tue</th>
            <th>Wed</th>
            <th>Thu</th>
            <th>Fri</th>
            <th>Sat</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            @for($i = 0; $i < 7; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 7; $i < 14; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 14; $i < 21; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 21; $i < 28; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 28; $i < 35; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 35; $i < 42; $i++)
                <td class="day {{ $calendarData[$i]['isToday'] ? 'today' : '' }} {{ $calendarData[$i]['isWeekend'] ? 'weekend' : '' }} {{ $calendarData[$i]['isPast'] ? 'past' : '' }}"
                    data-date="{{ $calendarData[$i]['dateStr'] }}"
                    @if($calendarData[$i]['day'] && !$calendarData[$i]['isPast'])
                        onclick="selectDate('{{ $calendarData[$i]['dateStr'] }}', this)"
                    @endif>
                    <span class="date-number">{{ $calendarData[$i]['day'] ?: '' }}</span>
                    @if($calendarData[$i]['isMarked'])
                        <div class="dot holiday-dot"></div>
                    @endif
                    <div class="scheduled-dot" style="display: none;"></div>
                </td>
            @endfor
        </tr>
    </tbody>
</table>