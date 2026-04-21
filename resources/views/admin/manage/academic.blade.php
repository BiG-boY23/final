@extends('layouts.app')

@section('title', 'Manage Academic Data')
@section('subtitle', 'Manage colleges, departments, and courses to ensure registration forms remain current.')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">

    {{-- ── Filter & Search Control Bar ── --}}
    <div class="stat-card-premium no-print" style="padding: 1.5rem; display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center; border-radius: 20px;">
        <div style="flex: 1; min-width: 250px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.5rem;">Quick Search</label>
            <div style="position: relative;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" id="academicSearch" placeholder="Search colleges, departments, or programs..." 
                       style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 700; color: #1e293b; outline: none; transition: 0.3s;"
                       onkeyup="filterAcademicData()">
            </div>
        </div>

        <div style="width: 220px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.5rem;">Sort Grid By</label>
            <select id="academicSort" onchange="filterAcademicData()" style="width: 100%; padding: 0.75rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 700; color: #1e293b; outline: none; cursor: pointer;">
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="newest">Newest Added</option>
            </select>
        </div>
    </div>
    
    <!-- Section 1: Colleges / Departments -->
    <div class="table-container shadow-premium" style="border-radius: 20px; background: white; border: 1px solid #e2e8f0; overflow: hidden;">
        <div class="section-header" style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-buildings" style="font-size: 1.2rem; color: #741b1b;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">Academic Departments</h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;">Manage colleges and academic units</p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="showAddCollegeModal('academic')" style="background: #741b1b; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph ph-plus-circle" style="font-size: 1.1rem;"></i> Add Department
            </button>
        </div>

        <div class="table-wrapper">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Unit Name</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Type</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Programs</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($colleges as $college)
                    <tr style="border-bottom: 1px solid #f1f5f9;" data-name="{{ strtolower($college->name) }}" data-code="{{ strtolower($college->code) }}" data-date="{{ $college->created_at->timestamp }}">
                        <td style="padding: 1.25rem 2rem;">
                            <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">{{ $college->name }}</div>
                            <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">ID: #{{ str_pad($college->id, 3, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="background: #ecfdf5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Academic</span>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #475569; font-size: 0.85rem;">
                                <i class="ph ph-graduation-cap"></i> {{ $college->courses_count }} Programs
                            </div>
                        </td>
                        <td style="padding: 1.25rem 2rem; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button class="btn-icon" onclick="showEditCollegeModal({{ json_encode($college) }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-icon" onclick="confirmDeleteCollege({{ $college->id }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #fee2e2; background: #fff1f1; color: #dc2626; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                            <form id="delete-college-{{ $college->id }}" action="{{ route('admin.manage.academic.college.destroy', $college->id) }}" method="POST" style="display: none;">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600;">No academic departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Administrative Offices -->
    <div class="table-container shadow-premium" style="border-radius: 20px; background: white; border: 1px solid #e2e8f0; overflow: hidden;">
        <div class="section-header" style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-briefcase" style="font-size: 1.2rem; color: #1e40af;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">Administrative Offices</h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;">Manage non-academic service units</p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="showAddCollegeModal('administrative')" style="background: #1e40af; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph ph-plus-circle" style="font-size: 1.1rem;"></i> Add Office
            </button>
        </div>

        <div class="table-wrapper">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Office Name</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Type</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                    <tr style="border-bottom: 1px solid #f1f5f9;" data-name="{{ strtolower($office->name) }}" data-code="{{ strtolower($office->code ?? '') }}" data-date="{{ $office->created_at->timestamp }}">
                        <td style="padding: 1.25rem 2rem;">
                            <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">{{ $office->name }}</div>
                            <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">ID: #{{ str_pad($office->id, 3, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="background: #f0f9ff; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Administrative</span>
                        </td>
                        <td style="padding: 1.25rem 2rem; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button class="btn-icon" onclick="showEditCollegeModal({{ json_encode($office) }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-icon" onclick="confirmDeleteCollege({{ $office->id }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #fee2e2; background: #fff1f1; color: #dc2626; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                            <form id="delete-office-{{ $office->id }}" action="{{ route('admin.manage.academic.college.destroy', $office->id) }}" method="POST" style="display: none;">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600;">No administrative offices configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Courses / Programs -->
    <div class="table-container shadow-premium" style="border-radius: 20px; background: white; border: 1px solid #e2e8f0; overflow: hidden;">
        <div class="section-header" style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #fefce8; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph-bold ph-graduation-cap" style="font-size: 1.2rem; color: #854d0e;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">Degree Programs / Courses</h3>
                    <p style="margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 600;">Manage specific undergraduate programs</p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="showAddCourseModal()" style="background: #741b1b; color: white; border: none; padding: 0.6rem 1.25rem; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <i class="ph ph-plus-circle" style="font-size: 1.1rem;"></i> Add Program
            </button>
        </div>

        <div class="table-wrapper">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Program Name</th>
                        <th style="padding: 1rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Station/Dept</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr style="border-bottom: 1px solid #f1f5f9;" data-name="{{ strtolower($course->name) }}" data-code="{{ strtolower($course->code ?? '') }}" data-parent="{{ strtolower($course->college->name) }}" data-date="{{ $course->created_at->timestamp }}">
                        <td style="padding: 1.25rem 2rem;">
                            <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">{{ $course->name }}</div>
                            @if($course->code)
                                <div style="font-size: 0.7rem; color: #741b1b; font-weight: 800; text-transform: uppercase;">Code: {{ $course->code }}</div>
                            @endif
                        </td>
                        <td style="padding: 1rem;">
                            <div style="font-weight: 700; color: #475569; font-size: 0.85rem;">{{ $course->college->name }}</div>
                        </td>
                        <td style="padding: 1.25rem 2rem; text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <button class="btn-icon" onclick="showEditCourseModal({{ json_encode($course) }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn-icon" onclick="confirmDeleteCourse({{ $course->id }})" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid #fee2e2; background: #fff1f1; color: #dc2626; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                            <form id="delete-course-{{ $course->id }}" action="{{ route('admin.manage.academic.course.destroy', $course->id) }}" method="POST" style="display: none;">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding: 3rem; text-align: center; color: #94a3b8; font-weight: 600;">No programs configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    /** ── Unified Filtering & Sorting Engine ── **/
    function filterAcademicData() {
        const query = document.getElementById('academicSearch').value.toLowerCase();
        const sort = document.getElementById('academicSort').value;
        const allRows = document.querySelectorAll('tbody tr[data-name]');

        allRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const code = row.getAttribute('data-code');
            const parent = row.getAttribute('data-parent') || ''; // Only for courses
            
            const matches = name.includes(query) || code.includes(query) || parent.includes(query);
            row.style.display = matches ? '' : 'none';
        });

        // Trigger sort for each table
        sortTables(sort);
    }

    function filterSectionTable(input) {
        const query = input.value.toLowerCase();
        const type = input.getAttribute('data-type');
        const rows = input.closest('.table-container').querySelectorAll('tbody tr[data-name]');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const code = row.getAttribute('data-code');
            const matches = name.includes(query) || code.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    }

    function sortTables(criteria) {
        const tables = document.querySelectorAll('.table-wrapper table');
        
        tables.forEach(table => {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-name]'));

            rows.sort((a, b) => {
                const nameA = a.getAttribute('data-name');
                const nameB = b.getAttribute('data-name');
                const dateA = parseInt(a.getAttribute('data-date'));
                const dateB = parseInt(b.getAttribute('data-date'));

                if (criteria === 'name-asc') return nameA.localeCompare(nameB);
                if (criteria === 'name-desc') return nameB.localeCompare(nameA);
                if (criteria === 'newest') return dateB - dateA;
                return 0;
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    }

    function showAddCollegeModal(category = 'academic') {
        const title = category === 'academic' ? 'Add New Department' : 'Add New Office';
        const label = category === 'academic' ? 'Department Name' : 'Office Name';
        
        Swal.fire({
            title: title,
            html: `
                <form id="addCollegeForm" action="{{ route('admin.manage.academic.college.store') }}" method="POST" style="text-align: left;">
                    @csrf
                    <input type="hidden" name="category" value="${category}">
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">${label}</label>
                        <input type="text" name="name" class="swal2-input custom-swal-input" placeholder="e.g. ${category === 'academic' ? 'College of Computing' : 'Registrar Office'}" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">${category === 'academic' ? 'Code' : 'Alternate Identifier'} (Optional)</label>
                        <input type="text" name="code" class="swal2-input custom-swal-input" placeholder="e.g. ${category === 'academic' ? 'COC' : 'ADMIN'}">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Entry',
            confirmButtonColor: category === 'academic' ? '#741b1b' : '#1e40af',
            preConfirm: () => {
                const form = document.getElementById('addCollegeForm');
                if (!form.checkValidity()) { form.reportValidity(); return false; }
                form.submit();
            }
        });
    }

    function showEditCollegeModal(college) {
        const title = college.category === 'academic' ? 'Edit Department' : 'Edit Office';
        const label = college.category === 'academic' ? 'Department Name' : 'Office Name';

        Swal.fire({
            title: title,
            html: `
                <form id="editCollegeForm" action="{{ url('admin/manage/academic/college') }}/${college.id}" method="POST" style="text-align: left;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="category" value="${college.category}">
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">${label}</label>
                        <input type="text" name="name" class="swal2-input custom-swal-input" value="${college.name}" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Identifier / Code</label>
                        <input type="text" name="code" class="swal2-input custom-swal-input" value="${college.code || ''}">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update Entry',
            confirmButtonColor: '#741b1b',
            preConfirm: () => {
                const form = document.getElementById('editCollegeForm');
                if (!form.checkValidity()) { form.reportValidity(); return false; }
                form.submit();
            }
        });
    }

    function showAddCourseModal() {
        Swal.fire({
            title: 'Add New Course Program',
            html: `
                <form id="addCourseForm" action="{{ route('admin.manage.academic.course.store') }}" method="POST" style="text-align: left;">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Assign to Department</label>
                        <select name="college_id" class="swal2-select custom-swal-input" required>
                            @foreach($colleges as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Degree Program Name</label>
                        <input type="text" name="name" class="swal2-input custom-swal-input" placeholder="e.g. BS in Information Technology" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Program Abbreviation (Code)</label>
                        <input type="text" name="code" class="swal2-input custom-swal-input" placeholder="e.g. BSIT">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Create Program',
            confirmButtonColor: '#741b1b',
            preConfirm: () => {
                const form = document.getElementById('addCourseForm');
                if (!form.checkValidity()) { form.reportValidity(); return false; }
                form.submit();
            }
        });
    }

    function showEditCourseModal(course) {
        Swal.fire({
            title: 'Edit Course Program',
            html: `
                <form id="editCourseForm" action="{{ url('admin/manage/academic/course') }}/${course.id}" method="POST" style="text-align: left;">
                    @csrf
                    @method('PUT')
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Assign to Department</label>
                        <select name="college_id" class="swal2-select custom-swal-input" required>
                            @foreach($colleges as $c)
                                <option value="{{ $c->id }}" ${course.college_id == {{ $c->id }} ? 'selected' : ''}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Degree Program Name</label>
                        <input type="text" name="name" class="swal2-input custom-swal-input" value="${course.name}" required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label class="form-label">Program Abbreviation (Code)</label>
                        <input type="text" name="code" class="swal2-input custom-swal-input" value="${course.code || ''}">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update Program',
            confirmButtonColor: '#741b1b',
            preConfirm: () => {
                const form = document.getElementById('editCourseForm');
                if (!form.checkValidity()) { form.reportValidity(); return false; }
                form.submit();
            }
        });
    }

    function confirmDeleteCollege(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Deleting this college will also delete all associated programs and courses. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#741b1b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, purge it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-college-' + id).submit();
            }
        });
    }

    function confirmDeleteCourse(id) {
        Swal.fire({
            title: 'Remove Program?',
            text: "Are you sure you want to delete this course program from the registry?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#741b1b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-course-' + id).submit();
            }
        });
    }
</script>

<style>
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .section-header h3 { margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b; }
    .badge { padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .custom-swal-input { width: 100% !important; margin: 0 !important; height: 38px !important; font-size: 0.9rem !important; }
    .form-label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.3rem; color: #475569; }
</style>
@endsection
