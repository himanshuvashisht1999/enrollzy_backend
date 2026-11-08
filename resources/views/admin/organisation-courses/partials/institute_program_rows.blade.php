{{-- one row of the Batches / Year-wise Results / Toppers tables of institute_program_extras. $row: batch | result | topper;
     $i: the row's index ('__i__' in the template the Add button copies); $d: the row's values. --}}
@php
    $PF = \App\Support\Institutes\ProgramFields::class;
    $d = (array) ($d ?? []);
    $val = fn ($k) => $d[$k] ?? '';
    $opts = fn ($list, $cur, $blank = true) => ($blank ? '<option value=""></option>' : '') . collect($list)->map(fn ($o) => '<option value="' . e($o) . '"' . ((string) $cur === $o ? ' selected' : '') . '>' . e($o) . '</option>')->implode('');
    $remove = '<td><button type="button" class="btn btn-sm btn-outline-danger px-remove" title="Remove">&times;</button></td>';
@endphp
@if($row === 'batch')
    <tr>
        <td><input type="text" name="batches[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $val('name') }}" maxlength="255" placeholder="e.g. JEE 2027 Morning A"></td>
        <td><input type="date" name="batches[{{ $i }}][start_date]" class="form-control form-control-sm" value="{{ $val('start_date') }}"></td>
        <td><select name="batches[{{ $i }}][timing]" class="form-select form-select-sm">{!! $opts($PF::BATCH_TIMINGS, $val('timing')) !!}</select></td>
        <td><input type="text" name="batches[{{ $i }}][timing_details]" class="form-control form-control-sm" value="{{ $val('timing_details') }}" maxlength="100" placeholder="e.g. 7–10 AM"></td>
        <td><select name="batches[{{ $i }}][medium]" class="form-select form-select-sm">{!! $opts($PF::BATCH_MEDIUMS, $val('medium')) !!}</select></td>
        <td><input type="number" name="batches[{{ $i }}][total_seats]" class="form-control form-control-sm" value="{{ $val('total_seats') }}" min="0"></td>
        <td><input type="number" name="batches[{{ $i }}][seats_left]" class="form-control form-control-sm" value="{{ $val('seats_left') }}" min="0"></td>
        <td><select name="batches[{{ $i }}][status]" class="form-select form-select-sm">{!! $opts($PF::BATCH_STATUSES, $val('status') ?: 'Open', false) !!}</select></td>
        {!! $remove !!}
    </tr>
@elseif($row === 'result')
    <tr>
        <td><input type="number" name="results[{{ $i }}][year]" class="form-control form-control-sm" value="{{ $val('year') }}" min="1980" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}"></td>
        <td><input type="number" name="results[{{ $i }}][selections]" class="form-control form-control-sm" value="{{ $val('selections') }}" min="0"></td>
        <td><input type="number" name="results[{{ $i }}][top_rank]" class="form-control form-control-sm" value="{{ $val('top_rank') }}" min="0"></td>
        <td><input type="text" name="results[{{ $i }}][notes]" class="form-control form-control-sm" value="{{ $val('notes') }}" maxlength="255" placeholder="e.g. 12 in top 100"></td>
        {!! $remove !!}
    </tr>
@else
    <tr>
        <td>
            <input type="hidden" name="toppers[{{ $i }}][id]" value="{{ $val('id') }}">
            <div class="d-flex align-items-center gap-2">
                @if(!empty($photo))
                    <img src="{{ asset($photo) }}" alt="" style="width:40px;height:40px;object-fit:cover" class="rounded border">
                @endif
                <input type="file" name="toppers[{{ $i }}][photo]" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp">
            </div>
            @if(!empty($photo))
                <div class="form-check small mt-1">
                    <input class="form-check-input" type="checkbox" name="toppers[{{ $i }}][remove_photo]" value="1" id="px_tp_rm_{{ $i }}">
                    <label class="form-check-label" for="px_tp_rm_{{ $i }}">Remove photo</label>
                </div>
            @endif
        </td>
        <td><input type="text" name="toppers[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $val('name') }}" maxlength="255"></td>
        <td><input type="number" name="toppers[{{ $i }}][rank]" class="form-control form-control-sm" value="{{ $val('rank') }}" min="0"></td>
        <td><input type="text" name="toppers[{{ $i }}][exam]" class="form-control form-control-sm" value="{{ $val('exam') }}" maxlength="100" placeholder="e.g. JEE Advanced"></td>
        <td><input type="number" name="toppers[{{ $i }}][year]" class="form-control form-control-sm" value="{{ $val('year') }}" min="1980" max="{{ date('Y') + 1 }}"></td>
        <td>
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="toppers[{{ $i }}][consent]" value="1" id="px_tp_c_{{ $i }}" {{ !empty($d['consent']) ? 'checked' : '' }}>
                <label class="form-check-label small" for="px_tp_c_{{ $i }}">Agreed</label>
            </div>
        </td>
        {!! $remove !!}
    </tr>
@endif
