{{-- Programme inputs that the type's own block above does not draw. Which ones, and how, comes from App\Support\OrganisationFields\CourseFields.
     $only: draw just these of them; $bare: only the inputs (for a tab of the university / college form, inside its row). --}}
@php
    $extraNames = \App\Support\OrganisationFields\CourseFields::extra((int) $typeId);
    if (isset($only)) $extraNames = array_values(array_intersect($extraNames, $only));
    $bare = !empty($bare);
    $labels = app(\App\Services\OrganisationFieldCatalogService::class)->getFieldLabels('course', (int) $typeId);
    $defs = \App\Support\OrganisationFields\CourseFields::DEFS;
@endphp
@if(count($extraNames))
    @unless($bare)
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="fw-bold mb-0 text-primary">More programme details</h5>
                <div class="small text-muted">Fees, admissions, academic masters and, where they apply to this type of organisation, school or coaching details. All optional.</div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
    @endunless
                    @foreach($extraNames as $name)
                        @php
                            $def = $defs[$name];
                            $kind = $def[0];
                            $col = $def['col'] ?? ($kind === 'textarea' ? 12 : ($kind === 'switch' ? 4 : 4));
                            $label = $labels[$name] ?? ucfirst(str_replace('_', ' ', $name));
                            $value = old($name, isset($organisationCourse) ? $organisationCourse->$name : null);
                        @endphp
                        <div class="col-md-{{ $col }}">
                            @if($kind === 'switch')
                                <div class="form-check form-switch mt-4">
                                    <input type="hidden" name="{{ $name }}" value="0">
                                    <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" id="cx_{{ $name }}" {{ $value ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cx_{{ $name }}">{{ $label }}</label>
                                </div>
                            @else
                                <label class="form-label fw-bold" for="cx_{{ $name }}">{{ $label }}</label>
                                @if($kind === 'textarea')
                                    <textarea name="{{ $name }}" id="cx_{{ $name }}" class="form-control" rows="3">{{ $value }}</textarea>
                                @elseif($kind === 'number')
                                    <input type="number" min="0" name="{{ $name }}" id="cx_{{ $name }}" class="form-control" value="{{ $value }}"
                                        @if(!empty($def['step'])) step="{{ $def['step'] }}" @endif @if(isset($def['max'])) max="{{ $def['max'] }}" @endif @if(!empty($def['ph'])) placeholder="{{ $def['ph'] }}" @endif>
                                @elseif($kind === 'multiselect')
                                    @php $picked = \App\Support\OrganisationFields\CourseFields::listValues($value); @endphp
                                    {{-- the empty value first: with nothing ticked the field is still sent, so it can be cleared --}}
                                    <input type="hidden" name="{{ $name }}" value="">
                                    <select name="{{ $name }}[]" id="cx_{{ $name }}" class="form-select select2" multiple style="width:100%" data-placeholder="Select one or more">
                                        @foreach(array_unique(array_merge($def['options'], $picked)) as $opt)
                                            <option value="{{ $opt }}" @selected(in_array($opt, $picked, true))>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Pick all that apply.</div>
                                @elseif($kind === 'select')
                                    <select name="{{ $name }}" id="cx_{{ $name }}" class="form-select">
                                        <option value="">Select</option>
                                        @foreach($def['options'] as $opt)
                                            <option value="{{ $opt }}" {{ (string) $value === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                        @if($value !== null && $value !== '' && !in_array($value, $def['options'], true))
                                            <option value="{{ $value }}" selected>{{ $value }}</option>
                                        @endif
                                    </select>
                                @elseif($kind === 'master')
                                    <select name="{{ $name }}" id="cx_{{ $name }}" class="form-select">
                                        <option value="">Select</option>
                                        @foreach($def['model']::where('status', 1)->orderBy('sort_order')->orderBy('title')->get(['id', 'title']) as $m)
                                            <option value="{{ $m->id }}" {{ (string) $value === (string) $m->id ? 'selected' : '' }}>{{ $m->title }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" name="{{ $name }}" id="cx_{{ $name }}" class="form-control" value="{{ $value }}" @if(!empty($def['ph'])) placeholder="{{ $def['ph'] }}" @endif>
                                @endif
                                @if(!empty($def['hint']))<div class="form-text">{{ $def['hint'] }}</div>@endif
                            @endif
                        </div>
                    @endforeach
    @unless($bare)
                </div>
            </div>
        </div>
    </div>
    @endunless
@endif
