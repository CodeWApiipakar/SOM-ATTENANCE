




@extends('layouts.app')

@section('title', 'Departments')
@section('content')

<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#departmentModal">New Department</button>

{{-- modal --}}
<x-modal id="departmentModal" title="Manage department" size="modal-sm" data-default-oldTitle="Manage department">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            
            <div class="col-sm-12">
                <label>company</label>
               <select name="company" id="company" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-12">
                <label>department Name</label>
                <input type="text" name="departmentName" id="departmentName" class="form-control form-control-sm">
            </div>

            <div class="col-sm-12">
                <label>Code</label>
                <input type="text" name="departmentCode" id="departmentCode" class="form-control form-control-sm">
            </div>

        </div>
        <input type="hidden" id="departmentId"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnSavedepartment" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>



{{-- table --}}
<div class="table-responsive-sm w-100 mt-2 card p-2">
    <table id="departmentTable" class="table table-hover table-sm dataTable" style="font-size: 10pt; border-collapse: collapse; width: 1567px;" cellspacing="0" border="1" rules="all">
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
    let companydropdown = $("#company")
    fillCompany(companydropdown)
});

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    //delete company
    function deleteDepartment(id) {
        showConfirmModal(`/department/${id}`, function() {
            getdepartmentes(); // refresh after success
        },`Are you sure you want to delete this department ${id}`);
    }


    // 🟩Edit company
    function editDepartment(id) {
        $.ajax({
            url: '/department/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const department = response.data;

                    // fill modal inputs
                    $("#departmentId").val(department.id);
                    $("#departmentName").val(department.name);
                    $("#departmentCode").val(department.code);
                    $("#company").val(department.companyId);
                    // change modal title and button text (optional)
                    $("#departmentModal .modal-title").text("Edit Department");
                    $("#btnSavedepartment").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function newCompany() {
    $("#departmentId").val('');
    $("#departmentName").val('');
    $("#departmentCode").val('');
    $("#departmentModal .modal-title").text("Add Department");
    $("#btnSavedepartment").text("Save");
    }

    //save
    $("#btnSavedepartment").on("click", function() {
    const id = $("#departmentId").val(); // check if editing

    const fields = [
            { id: "departmentName", label: "Name" },
            { id: "departmentCode", label: "code" },
            { id: "company", label: "Company" },
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
        name: $("#departmentName").val(),
        code: $("#departmentCode").val(),
        company: $("#company").val()
    };

    // Simple validation
    if (!data.name || !data.company || !data.code) {
       showAlert('Please fill all fields', 'warning');
        return;
    }

    // Determine endpoint and method
    const url = id ? `/department/${id}` : `/department`;
    const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully',"success");
                    $('#departmentModal').modal('hide');
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
    $('#departmentTable thead').empty();
    $('#departmentTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#departmentTable')) {
        $('#departmentTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getdepartments',
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
                $('#departmentTable thead').html(theadHtml);

                // 🔹 Step 3: Build table rows dynamically
                let rows = '';
                data.forEach(department => {
                    rows += '<tr>';
                    keys.forEach(key => {
                        rows += `<td>${department[key] ?? ''}</td>`;
                    });

                    // Add edit/delete icons
                    rows += `
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="editDepartment(${department.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#departmentModal"></i>
                        </td>
                        <td>
                            <i class="bi bi-trash text-danger" 
                               style="cursor:pointer"
                               onclick="deleteDepartment(${department.id})"></i>
                        </td>
                    `;
                    rows += '</tr>';
                });

                // 🔹 Step 4: Render data
                $('#departmentTable tbody').html(rows);

                // 🔹 Step 5: Optional DataTable initialization
                if (typeof initializeDataTable === 'function') {
                    initializeDataTable("#departmentTable", true, [0,1,2]);
                }

            } else {
                // No data fallback
                $('#departmentTable thead').html('<tr><th>No Data Found</th></tr>');
                $('#departmentTable tbody').html('');
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