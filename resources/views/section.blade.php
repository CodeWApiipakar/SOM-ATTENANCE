




@extends('layouts.app')

@section('title', 'Sections')
@section('content')

<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#sectionModal">New Section</button>

{{-- modal --}}
<x-modal id="sectionModal" title="Manage section" size="modal-sm" data-default-oldTitle="Manage section">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            
            <div class="col-sm-12">
                <label>Department</label>
               <select name="department" id="department" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-12">
                <label>section Name</label>
                <input type="text" name="sectionName" id="sectionName" class="form-control form-control-sm">
            </div>

            <div class="col-sm-12">
                <label>Code</label>
                <input type="text" name="sectionCode" id="sectionCode" class="form-control form-control-sm">
            </div>

        </div>
        <input type="hidden" id="sectionId"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnSaveSection" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>



{{-- table --}}
<div class="table-responsive-sm w-100 mt-2 card p-2">
    <table id="sectionTable" class="table table-hover table-sm"  style="font-size: 10pt; border-collapse: collapse; width: 1567px;" cellspacing="0" border="1" rules="all">
        <thead>
        </thead>
        <tbody>
        </tbody>
    </table>
    <x-loading-spinner id="tableSpinner" />
</div>

@endsection

@push('scripts')

<script>
  $(document).ready(() => {
    getdepartmentes()
    let departmentdropdown = $("#department")
    fillDepartment(departmentdropdown,"%")
});

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    //delete department
    function deleteSection(id) {
        showConfirmModal(`/section/${id}`, function() {
            getdepartmentes(); // refresh after success
        },`Are you sure you want to delete this department ${id}`);
    }


    // 🟩Edit department
    function editSection(id) {
        $.ajax({
            url: '/section/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const section = response.data;

                    // fill modal inputs
                    $("#sectionId").val(section.id);
                    $("#sectionName").val(section.name);
                    $("#sectionCode").val(section.code);
                    $("#department").val(section.deparmentId);
                    // change modal title and button text (optional)
                    $("#sectionModal .modal-title").text("Edit section");
                    $("#btnSaveSection").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function newdepartment() {
    $("#sectionId").val('');
    $("#sectionName").val('');
    $("#sectionCode").val('');
    $("#sectionModal .modal-title").text("Add section");
    $("#btnSaveSection").text("Save");
    }

    //save
    $("#btnSaveSection").on("click", function() {
    const id = $("#sectionId").val(); // check if editing

    const fields = [
            { id: "sectionName", label: "Name" },
            { id: "sectionCode", label: "code" },
            { id: "department", label: "department" },
        ];

        let hasError = false;

        // 🔹 Remove old errors
        $(".error-text").remove();
        $(".is-invalid").removeClass("is-invalid");

        // 🔹 Validate fields
        fields.forEach(field => {
            const input = $("#" + field.id);
            if (!input.val()) {
                hasError = true;
                input.addClass("is-invalid");
                input.after(`<span class="error-text text-danger small">* ${field.label} is required</span>`);
            }
        });

        if (hasError) {
            showAlert('Please fill all required fields', 'warning');
            return;
        }

    const data = {
        name: $("#sectionName").val(),
        code: $("#sectionCode").val(),
        department: $("#department").val()
    };

    // Simple validation
    if (!data.name || !data.department || !data.code) {
       showAlert('Please fill all fields', 'warning');
        return;
    }

    // Determine endpoint and method
    const url = id ? `/section/${id}` : `/section`;
    const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully',"success");
                    $('#sectionModal').modal('hide');
                    getdepartmentes(); // reload table
                } else {
                    let msg =  response.message || 'Something went wrong'
                   showAlert(msg, 'error');
                }
            },
            error: function(xhr) {
                msg ="some error occured"

               showAlert(msg, 'error');
            }
        });
    });

    
    function getdepartmentes() {
    showSpinner('tableSpinner');
    $('#sectionTable thead').empty();
    $('#sectionTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#sectionTable')) {
        $('#sectionTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getSections',
        type: 'GET',
        success: function(response) {


            if (response.status === 200 && response.data.length > 0) {
                const data = response.data;

                // 🔹 Step 1: Get keys (column names)
                const keys = Object.keys(data[0]);

                // 🔹 Step 2: Build table headers dynamically
                let theadHtml = '<tr>';
                keys.forEach(key => {
                    const label = key.replace(/_/g, ' ')        // change snake_case to readable
                                     .replace(/\b\w/g, c => c.toUpperCase()); // capitalize
                    theadHtml += `<th>${label}</th>`;
                });
                theadHtml += '<th>Edit</th><th>Delete</th></tr>';
                $('#sectionTable thead').html(theadHtml);

                // 🔹 Step 3: Build table rows dynamically
                let rows = '';
                data.forEach(section => {
                    rows += '<tr>';
                    keys.forEach(key => {
                        rows += `<td>${section[key] ?? ''}</td>`;
                    });

                    // Add edit/delete icons
                    rows += `
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="editSection(${section.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#sectionModal"></i>
                        </td>
                        <td>
                            <i class="bi bi-trash text-danger" 
                               style="cursor:pointer"
                               onclick="deleteSection(${section.id})"></i>
                        </td>
                    `;
                    rows += '</tr>';
                });

                // 🔹 Step 4: Render data
                $('#sectionTable tbody').html(rows);

                // 🔹 Step 5: Optional DataTable initialization
                if (typeof initializeDataTable === 'function') {
                    initializeDataTable("#sectionTable", true, [0,1,2]);
                }

            } else {
                // No data fallback
                $('#sectionTable thead').html('<tr><th>No Data Found</th></tr>');
                $('#sectionTable tbody').html('');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
        },
        complete: function() {
            hideSpinner('tableSpinner');
        }
    });
}

</script>

@endpush