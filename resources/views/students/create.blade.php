@extends('layouts.app')

@section('title', 'New Admission')

@section('breadcrumb')
    <a href="{{ route('students.index') }}" class="bc-item">Students</a>
    <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
    <span class="bc-current">New Admission</span>
@endsection

@push('styles')
<style>
    /* ── Step Indicator ──────────────────────────────────── */
    .step-indicator {
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 32px; flex-wrap: wrap; gap: 0;
    }
    .step-circle {
        width: 38px; height: 38px; border-radius: 50%;
        background: #e9ecef; color: #adb5bd;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px;
        transition: all 0.3s ease; flex-shrink: 0;
    }
    .step-circle.active { background: #1E3A5F; color: #fff; box-shadow: 0 0 0 4px rgba(30,58,95,0.15); }
    .step-circle.done   { background: #27AE60; color: #fff; }
    .step-label { font-size: 12px; font-weight: 600; color: #6C757D; margin-top: 6px; text-align: center; }
    .step-label.active { color: #1E3A5F; }
    .step-line { width: 60px; height: 2px; background: #e9ecef; margin: 0 6px; transition: background 0.3s; }
    .step-line.done { background: #27AE60; }
    .step-item { display: flex; flex-direction: column; align-items: center; }

    /* ── Form Steps ───────────────────────────────────────── */
    .form-step { display: none; }
    .form-step.active { display: block; animation: fadeInStep 0.3s ease; }
    @keyframes fadeInStep { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .field-label { font-size: 13px; font-weight: 600; color: #2C3E50; margin-bottom: 6px; display: block; }
    .field-label .req { color: #E74C3C; }
    .form-control-custom {
        width: 100%; padding: 10px 14px; border: 2px solid #e9ecef;
        border-radius: 10px; font-size: 14px; transition: all 0.2s;
    }
    .form-control-custom:focus { border-color: #1E3A5F; box-shadow: 0 0 0 3px rgba(30,58,95,0.08); outline: none; }
    .form-control-custom.is-invalid { border-color: #E74C3C; }
    .field-error-text { font-size: 12px; color: #E74C3C; margin-top: 4px; display: none; }
    .field-error-text.show { display: block; }

    /* Photo dropzone */
    .photo-dropzone {
        border: 2px dashed #dee2e6; border-radius: 12px; padding: 24px;
        text-align: center; cursor: pointer; transition: all 0.2s;
        background: #fafafa;
    }
    .photo-dropzone:hover, .photo-dropzone.dragover { border-color: #1E3A5F; background: rgba(30,58,95,0.04); }
    .photo-preview { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; display: none; }

    /* Summary (Step 4) */
    .summary-section { margin-bottom: 18px; }
    .summary-section h6 { font-size: 13px; font-weight: 700; color: #1E3A5F; text-transform: uppercase;
        letter-spacing: 0.5px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #f0f0f0; }
    .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f5f5f5; }
    .summary-row .label { color: #6C757D; }
    .summary-row .value { font-weight: 600; color: #2C3E50; }
    .edit-step-link { font-size: 11px; color: #1E3A5F; cursor: pointer; text-decoration: underline; }

    @media (max-width: 576px) {
        .step-line { width: 30px; }
        .step-label { font-size: 10px; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="page-icon"><i class="fa-solid fa-user-plus"></i></span>
        New Admission
    </h1>
    <a href="{{ route('students.index') }}" class="btn btn-sm" style="background:#6C757D;color:#fff;border-radius:8px;">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card-custom">
    <div class="card-body-c">

        {{-- Step Indicator --}}
        <div class="step-indicator">
            <div class="step-item">
                <div class="step-circle active" id="circle-1">1</div>
                <div class="step-label active" id="label-1">Personal Info</div>
            </div>
            <div class="step-line" id="line-1"></div>
            <div class="step-item">
                <div class="step-circle" id="circle-2">2</div>
                <div class="step-label" id="label-2">Academic Record</div>
            </div>
            <div class="step-line" id="line-2"></div>
            <div class="step-item">
                <div class="step-circle" id="circle-3">3</div>
                <div class="step-label" id="label-3">Enrollment</div>
            </div>
            <div class="step-line" id="line-3"></div>
            <div class="step-item">
                <div class="step-circle" id="circle-4">4</div>
                <div class="step-label" id="label-4">Confirm</div>
            </div>
        </div>

        <form id="admissionForm" novalidate>
            @csrf

            {{-- ═══════════════ STEP 1: PERSONAL INFO ═══════════════ --}}
            <div class="form-step active" data-step="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="field-label">Full Name <span class="req">*</span></label>
                        <input type="text" class="form-control-custom" name="name" placeholder="e.g. Ahmad Ali Khan">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Father's Name <span class="req">*</span></label>
                        <input type="text" class="form-control-custom" name="father_name" placeholder="e.g. Muhammad Ali">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Date of Birth</label>
                        <input type="text" class="form-control-custom flatpickr-date" name="dob" placeholder="Select date">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">CNIC / B-Form Number</label>
                        <input type="text" class="form-control-custom" name="cnic_bform" placeholder="00000-0000000-0">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">WhatsApp Number <span class="req">*</span></label>
                        <input type="text" class="form-control-custom" name="whatsapp" placeholder="+92 3XX XXXXXXX">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="field-label">Alternate Phone</label>
                        <input type="text" class="form-control-custom" name="alternate_phone" placeholder="Optional">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-8">
                        <label class="field-label">Full Address</label>
                        <textarea class="form-control-custom" name="address" rows="2" placeholder="House #, Street, City"></textarea>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Previous School <span class="req">*</span></label>
                        <input type="text" class="form-control-custom" name="previous_school" placeholder="School name">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Student Photo</label>
                        <div class="photo-dropzone" id="photoDropzone">
                            <img id="photoPreview" class="photo-preview">
                            <i class="fa-solid fa-camera fa-2x text-muted mb-2" id="photoIcon"></i>
                            <p class="mb-0 small text-muted">Click or drag photo here<br>(JPG/PNG, max 2MB)</p>
                            <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png" hidden>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ STEP 2: ACADEMIC RECORD ═══════════════ --}}
            <div class="form-step" data-step="2">
                <h6 style="color:#1E3A5F;font-weight:700;font-size:14px;margin-bottom:14px;">
                    <i class="fa-solid fa-graduation-cap me-2"></i>9th Class Record
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="field-label">Board <span class="req">*</span></label>
                        <select class="form-control-custom" name="ninth_board">
                            <option value="">Select Board</option>
                            <option>BISE Rawalpindi</option>
                            <option>BISE Lahore</option>
                            <option>BISE Gujranwala</option>
                            <option>BISE Sargodha</option>
                            <option>Other</option>
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Roll Number</label>
                        <input type="text" class="form-control-custom" name="ninth_roll_no">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Year</label>
                        <input type="text" class="form-control-custom" name="ninth_year" placeholder="2024" maxlength="4">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Total Marks</label>
                        <input type="number" class="form-control-custom calc-marks" name="ninth_total_marks" data-pair="ninth">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Obtained</label>
                        <input type="number" class="form-control-custom calc-marks" name="ninth_obtained_marks" data-pair="ninth">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Stream</label>
                        <select class="form-control-custom" name="ninth_stream">
                            <option value="">—</option>
                            <option value="science">Science</option>
                            <option value="arts">Arts</option>
                            <option value="computer">Computer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Percentage</label>
                        <input type="text" class="form-control-custom" id="ninth_percent" readonly style="background:#f8f9fa;">
                    </div>
                </div>

                <h6 style="color:#1E3A5F;font-weight:700;font-size:14px;margin-bottom:14px;">
                    <i class="fa-solid fa-graduation-cap me-2"></i>10th Class Record
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="field-label">Board <span class="req">*</span></label>
                        <select class="form-control-custom" name="tenth_board">
                            <option value="">Select Board</option>
                            <option>BISE Rawalpindi</option>
                            <option>BISE Lahore</option>
                            <option>BISE Gujranwala</option>
                            <option>BISE Sargodha</option>
                            <option>Other</option>
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Roll Number</label>
                        <input type="text" class="form-control-custom" name="tenth_roll_no">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Year</label>
                        <input type="text" class="form-control-custom" name="tenth_year" placeholder="2025" maxlength="4">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Total Marks</label>
                        <input type="number" class="form-control-custom calc-marks" name="tenth_total_marks" data-pair="tenth">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Obtained</label>
                        <input type="number" class="form-control-custom calc-marks" name="tenth_obtained_marks" data-pair="tenth">
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Stream</label>
                        <select class="form-control-custom" name="tenth_stream">
                            <option value="">—</option>
                            <option value="science">Science</option>
                            <option value="arts">Arts</option>
                            <option value="computer">Computer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">Percentage</label>
                        <input type="text" class="form-control-custom" id="tenth_percent" readonly style="background:#f8f9fa;">
                    </div>
                </div>
            </div>

            {{-- ═══════════════ STEP 3: ENROLLMENT ═══════════════ --}}
            <div class="form-step" data-step="3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="field-label">Campus <span class="req">*</span></label>
                        <select class="form-control-custom" name="campus" id="selCampus">
                            <option value="">Select Campus</option>
                            <option value="boys">Boys Campus</option>
                            <option value="girls">Girls Campus</option>
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Academic Year <span class="req">*</span></label>
                        <select class="form-control-custom" name="year" id="selYear">
                            <option value="">Select Year</option>
                            <option value="first">First Year</option>
                            <option value="second">Second Year</option>
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Program <span class="req">*</span></label>
                        <select class="form-control-custom" name="program_id" id="selProgram">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" data-scope="{{ $program->campus_scope }}">{{ $program->code }} — {{ $program->name }}</option>
                            @endforeach
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="field-label">Section <span class="req">*</span></label>
                        <select class="form-control-custom" name="section_id" id="selSection" disabled>
                            <option value="">Select Campus/Year/Program first</option>
                        </select>
                        <div class="field-error-text"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="field-label">Enrollment Date <span class="req">*</span></label>
                        <input type="text" class="form-control-custom flatpickr-date" name="enrollment_date" id="enrollDate">
                        <div class="field-error-text"></div>
                    </div>
                </div>
                <div class="alert alert-info mt-3" style="border-radius:10px;font-size:13px;background:#f0f8ff;border:none;color:#1a5276;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    FAIT program is only available when <strong>Girls Campus</strong> is selected. Section list updates automatically once Campus + Year + Program are chosen.
                </div>
            </div>

            {{-- ═══════════════ STEP 4: CONFIRM ═══════════════ --}}
            <div class="form-step" data-step="4">
                <div id="summaryContainer"></div>
            </div>

            {{-- ── NAVIGATION BUTTONS ──────────────────────────── --}}
            <div class="d-flex justify-content-between mt-4 pt-3" style="border-top:1px solid #f0f0f0;">
                <button type="button" class="btn" id="btnPrev" style="background:#6C757D;color:#fff;border-radius:8px;padding:10px 22px;display:none;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Previous
                </button>
                <div></div>
                <button type="button" class="btn" id="btnNext" style="background:#1E3A5F;color:#fff;border-radius:8px;padding:10px 22px;">
                    Next <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
                <button type="submit" class="btn" id="btnSubmit" style="background:#27AE60;color:#fff;border-radius:8px;padding:10px 22px;display:none;">
                    <i class="fa-solid fa-check-circle me-1" id="submitIcon"></i>
                    <span id="submitText">Submit & Enroll</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(function () {
    flatpickr('.flatpickr-date', { dateFormat: 'Y-m-d', maxDate: 'today' });
    flatpickr('#enrollDate', { dateFormat: 'Y-m-d', defaultDate: 'today' });

    let currentStep = 1;
    const totalSteps = 4;

    // ── Step Navigation ─────────────────────────────────────────
    function goToStep(step) {
        $('.form-step').removeClass('active');
        $(`.form-step[data-step="${step}"]`).addClass('active');

        for (let i = 1; i <= totalSteps; i++) {
            const circle = $(`#circle-${i}`);
            const label  = $(`#label-${i}`);
            circle.removeClass('active done');
            label.removeClass('active');
            if (i < step) circle.addClass('done');
            else if (i === step) { circle.addClass('active'); label.addClass('active'); }
        }
        $('.step-line').each(function (idx) {
            $(this).toggleClass('done', (idx + 1) < step);
        });

        $('#btnPrev').toggle(step > 1);
        $('#btnNext').toggle(step < totalSteps);
        $('#btnSubmit').toggle(step === totalSteps);

        if (step === totalSteps) buildSummary();
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $('#btnNext').on('click', function () {
        if (validateStep(currentStep)) goToStep(currentStep + 1);
    });
    $('#btnPrev').on('click', function () { goToStep(currentStep - 1); });

    // ── Step Validation (client-side, basic required check) ─────
    function validateStep(step) {
        let valid = true;
        const requiredMap = {
            1: ['name', 'father_name', 'whatsapp', 'previous_school'],
            2: ['ninth_board', 'tenth_board'],
            3: ['campus', 'year', 'program_id', 'section_id', 'enrollment_date'],
        };
        (requiredMap[step] || []).forEach(name => {
            const field = $(`[name="${name}"]`);
            const errorBox = field.closest('.col-md-6, .col-md-4, .col-md-3, .col-md-2, .col-md-8').find('.field-error-text');
            if (!field.val()) {
                field.addClass('is-invalid');
                errorBox.text('This field is required.').addClass('show');
                valid = false;
            } else {
                field.removeClass('is-invalid');
                errorBox.removeClass('show');
            }
        });
        if (!valid) toastr.warning('Please fill all required fields before continuing.');
        return valid;
    }

    // ── Percentage Auto-Calculation ──────────────────────────────
    $('.calc-marks').on('input', function () {
        const pair = $(this).data('pair');
        const total = parseFloat($(`[name="${pair}_total_marks"]`).val()) || 0;
        const obtained = parseFloat($(`[name="${pair}_obtained_marks"]`).val()) || 0;
        $(`#${pair}_percent`).val(total > 0 ? ((obtained / total) * 100).toFixed(1) + '%' : '');
    });

    // ── Photo Upload Preview ──────────────────────────────────────
    $('#photoDropzone').on('click', () => $('#photoInput').click());
    $('#photoInput').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            toastr.error('Photo must be under 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            $('#photoPreview').attr('src', e.target.result).show();
            $('#photoIcon').hide();
        };
        reader.readAsDataURL(file);
    });

    // ── Campus/Year/Program → FAIT restriction + Section Loading ──
    function updateProgramOptions() {
        const campus = $('#selCampus').val();
        $('#selProgram option').show();
        $('#selProgram option').each(function () {
            const scope = $(this).data('scope');
            if (scope && scope !== 'both' && scope !== campus) $(this).hide();
        });
    }

    function loadSections() {
        const campus = $('#selCampus').val();
        const year = $('#selYear').val();
        const programId = $('#selProgram').val();
        const $section = $('#selSection');

        if (!campus || !year || !programId) {
            $section.html('<option value="">Select Campus/Year/Program first</option>').prop('disabled', true);
            return;
        }

        $section.html('<option value="">Loading sections...</option>').prop('disabled', true);

        $.get('{{ route("students.sections-for") }}', { campus, year, program_id: programId })
            .done(function (res) {
                if (res.success && res.data.length) {
                    let opts = '<option value="">Select Section</option>';
                    res.data.forEach(s => opts += `<option value="${s.id}">${s.label}</option>`);
                    $section.html(opts).prop('disabled', false);
                } else {
                    $section.html('<option value="">No sections found</option>').prop('disabled', true);
                }
            })
            .fail(function () {
                toastr.error('Could not load sections. Please try again.');
            });
    }

    $('#selCampus, #selYear, #selProgram').on('change', function () {
        updateProgramOptions();
        loadSections();
    });

    // ── Build Step 4 Summary ─────────────────────────────────────
    function buildSummary() {
        const f = $('#admissionForm');
        const val = name => f.find(`[name="${name}"]`).val() || '—';
        const selText = name => f.find(`[name="${name}"] option:selected`).text() || '—';

        const html = `
            <div class="summary-section">
                <h6><i class="fa-solid fa-user me-2"></i>Personal Information
                    <span class="edit-step-link float-end" onclick="window.goToStepFromSummary(1)">Edit</span>
                </h6>
                <div class="summary-row"><span class="label">Name</span><span class="value">${val('name')}</span></div>
                <div class="summary-row"><span class="label">Father's Name</span><span class="value">${val('father_name')}</span></div>
                <div class="summary-row"><span class="label">WhatsApp</span><span class="value">${val('whatsapp')}</span></div>
                <div class="summary-row"><span class="label">Previous School</span><span class="value">${val('previous_school')}</span></div>
            </div>
            <div class="summary-section">
                <h6><i class="fa-solid fa-graduation-cap me-2"></i>Academic Record
                    <span class="edit-step-link float-end" onclick="window.goToStepFromSummary(2)">Edit</span>
                </h6>
                <div class="summary-row"><span class="label">9th — Board / Marks</span><span class="value">${val('ninth_board')} / ${val('ninth_obtained_marks')}-${val('ninth_total_marks')}</span></div>
                <div class="summary-row"><span class="label">10th — Board / Marks</span><span class="value">${val('tenth_board')} / ${val('tenth_obtained_marks')}-${val('tenth_total_marks')}</span></div>
            </div>
            <div class="summary-section">
                <h6><i class="fa-solid fa-school me-2"></i>Enrollment Details
                    <span class="edit-step-link float-end" onclick="window.goToStepFromSummary(3)">Edit</span>
                </h6>
                <div class="summary-row"><span class="label">Campus</span><span class="value">${selText('campus')}</span></div>
                <div class="summary-row"><span class="label">Year</span><span class="value">${selText('year')}</span></div>
                <div class="summary-row"><span class="label">Program</span><span class="value">${selText('program_id')}</span></div>
                <div class="summary-row"><span class="label">Section</span><span class="value">${selText('section_id')}</span></div>
                <div class="summary-row"><span class="label">Enrollment Date</span><span class="value">${val('enrollment_date')}</span></div>
            </div>
            <div class="alert" style="background:#fff3cd;border:none;border-radius:10px;font-size:13px;color:#856404;">
                <i class="fa-solid fa-info-circle me-2"></i>
                System will auto-generate a unique Roll Number upon submission.
            </div>
        `;
        $('#summaryContainer').html(html);
    }

    window.goToStepFromSummary = function (step) { goToStep(step); };

    // ── Form Submit (AJAX) ─────────────────────────────────────────
    $('#admissionForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const $btn = $('#btnSubmit');
        $btn.prop('disabled', true);
        $('#submitIcon').attr('class', 'fa-solid fa-spinner fa-spin me-1');
        $('#submitText').text('Enrolling...');

        $.ajax({
            url: '{{ route("students.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
        }).done(function (res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Student Enrolled!',
                    html: `Roll Number: <strong style="font-size:18px;color:#1E3A5F;">${res.roll_number}</strong>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-user-plus me-1"></i> Enroll Another',
                    cancelButtonText: '<i class="fa-solid fa-eye me-1"></i> View Profile',
                    confirmButtonColor: '#1E3A5F',
                    cancelButtonColor: '#27AE60',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    } else {
                        window.location.href = `/students/${res.student_id}`;
                    }
                });
            }
        }).fail(function (xhr) {
            const res = xhr.responseJSON;
            if (xhr.status === 422 && res.errors) {
                let firstError = null;
                Object.keys(res.errors).forEach(field => {
                    const input = $(`[name="${field}"]`);
                    input.addClass('is-invalid');
                    input.closest('.col-md-6, .col-md-4, .col-md-3, .col-md-2, .col-md-8')
                         .find('.field-error-text').text(res.errors[field][0]).addClass('show');
                    if (!firstError) firstError = field;
                });
                toastr.error('Please correct the highlighted errors.');
            } else {
                toastr.error(res?.message || 'Something went wrong. Please try again.');
            }
            $btn.prop('disabled', false);
            $('#submitIcon').attr('class', 'fa-solid fa-check-circle me-1');
            $('#submitText').text('Submit & Enroll');
        });
    });

    updateProgramOptions();
});
</script>
@endpush
