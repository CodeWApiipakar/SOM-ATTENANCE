

@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')
<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#employeeModal">New employee</button>

{{-- modal --}}
<x-modal id="employeeModal" title="Manage employee" size="modal-xl" data-default-oldTitle="Manage user">
    <x-slot:body>
        <div class="row mb-3">

            <div class="col-sm-3">
                <label>company</label>
               <select name="company" id="company" class="form-select form-select-sm"></select>
            </div>

             <div class="col-sm-3">
                <label>Branch</label>
               <select name="branch" id="branch" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-3">
                <label>Emp Code</label>
                <input type="text" name="emp_code" id="emp_code" class="form-control form-control-sm">
            </div>

            <div class="col-sm-3">
                <label>Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-sm">
            </div>
     
        </div>

        <div class="row mb-3">
            
            
            <div class="col-sm-3">
                <label>External ID</label>
                <input type="text" name="externalID" id="externalID" class="form-control form-control-sm">
            </div>
            
            <div class="col-sm-3">
                <label>Department</label>
               <select name="department" id="department" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-3">
                <label>Section</label>
               <select name="section" id="section" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-3">
                <label>Jop Title</label>
               <select name="jopTitle" id="jopTitle" class="form-select form-select-sm"></select>
            </div>

            
        </div>
        
        <div class="row mb-3">
            <div class="col-sm-3">
                <label>Place of birth</label>
                <input type="text" name="pob" id="pob" class="form-control form-control-sm">
            </div>
            
            <div class="col-sm-3">
                <label>Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control form-control-sm">
            </div>

            <div class="col-sm-3">
                <label>Phone</label>
                <input type="tel" name="phone" id="phone" class="form-control form-control-sm">
            </div>

            <div class="col-sm-3">
                <label>Salary</label>
                <input type="tel" name="salary" id="salary" class="form-control form-control-sm">
            </div>

            <div class="col-sm-3">
                <label>Bonus</label>
                <input type="tel" name="bonus" id="bonus" class="form-control form-control-sm">
            </div>

            <div class="col-sm-3 mt-3">
                <div class="form-check p-0 m-0">
                    <input type="checkbox" name="chkStatus" id="chkStatus">
                    <label>Status</label>
                </div>
            </div>
        </div>


        <input type="hidden" id="employeeId"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnEmployeeChanges" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>



{{-- table --}}
<div class="table-responsive table-responsive-sm card w-100 mt-2 p-2">
    <table id="userTable" class="table table-hover w-100 p-0 m-0"  style="font-size: 10pt; border-collapse: collapse; width: 1567px;" cellspacing="0" border="1" rules="all">
        <thead class="p-0 m-0 fw-bold" style="font-size:12px">
        </thead>
        <tbody  class="p-0 m-0"  style="font-size:12px">
        </tbody>
    </table>
    <x-loading-spinner id="tableSpinner" />
</div>

@endsection

@push("scripts")
<script>



$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        
    $(document).ready(() => {
        let companydropdown = $("#company")
        fillCompany(companydropdown)
         getemployee()
        handleDropdownChange("#company", "#branch", fillBranch);
        handleDropdownChange("#company", "#department", fillDepartment);
        handleDropdownChange("#department", "#section", fillSection);
    });

$("#company, #branch").on("change", function () {
    getEmpcode();
});

function getEmpcode() {
    const companyId = $("#company").val();
    const branchId = $("#branch").val();

    // Ensure both dropdowns have a selected value
    if (!companyId || !branchId) {
        console.warn("Company and Branch must both be selected");
        return;
    }

    // Call your API endpoint
    $.ajax({
        url: `/getMaxEmpCode/${companyId}/${branchId}`,
        type: "GET",
        success: function (response) {
            console.log(response);

            if (response.status === 200 && response.data) {
                // Example: Prefix returned from company+branch and next ID
                const { prefix, nextCode } = response.data;
                $("#empCode").val(`${prefix}${nextCode}`); // set to input field
            } else {
                $("#empCode").val("");
                alert("Failed to fetch employee code");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Error fetching employee code");
        },
    });
}


   
    //------------------------------------------- delete user -------------------------------
    function deleteRow(id) {
        showConfirmModal(`/employees/${id}`, function() {
            getemployee(); // refresh after success
        },`Are you sure you want to delete this row ID = ${id}`);
    }


    //-------------------------------------------- 🟩Edit user --------------------------------
    function edit(id) {
        $.ajax({
            url: '/employees/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const user = response.data;

                    // fill modal inputs
                    $("#company").val(user.id);
                    $("#branch").val(user.id);
                    $("#department").val(user.id);
                    $("#section").val(user.id);
                    $("#dop").val(user.id);
                    $("#pob").val(user.id);
                    $("#salary").val(user.id);
                    $("#externalID").val(user.id);
                    $("#emp_code").val(user.id);
                    $("#bonus").val(user.id);
                    $("#employeeId").val(user.id);
                    $("#name").val(user.name);
                    $("#Email").val(user.name);
                    $("#username").val(user.name);
                    $("#phone").val(user.name);
                    $("#chkIsAdmin").prop("checked", user.isAdmin == 1);
                    $("#chkisAllowed").prop("checked", user.isAllowed == 1);
                    $("#chkisPremiumPaid").prop("checked", user.isPremium == 1);
                    $("#chkEnableSms").prop("checked", user.enableSms == 1);

                    // change modal title and button text (optional)
                    $("#employeeModal .modal-title").text("Edit user");
                    $("#btnEmployeeChanges").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }
 
    function newUser() {
    $("#employeeId").val('');
    $("#name").val('');
    $("#dob").val('');
    $("#pob").val('');
    $("#salary").val('');
    $("#bonus").val('');
    $("#emp_code").val('');
    $("#phone").val('');
    $("#employeeModal .modal-title").text("Add user");
    $("#btnEmployeeChanges").text("Save");
    }

    //-------------------------------------------- save & update ------------------------------------------
    //-------------------------------------------------------------------------------------------save & update
    $("#btnEmployeeChanges").on("click", function() {
        const id = $("#employeeId").val();
        const fields = [
            { id: "name", label: "Name" },
            { id: "phone", label: "Phone" },
            { id: "company", label: "Company" },
            { id: "department", label: "department" },
            { id: "section", label: "section" },
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

        // 🔹 Prepare data
        const data = {
            username: $("#username").val(),
            name: $("#name").val(),
            email: $("#Email").val(),
            phone: $("#phone").val(),
            isAdmin: $("#chkIsAdmin").is(":checked") ? 1 : 0,
            isAllowed: $("#chkisAllowed").is(":checked") ? 1 : 0,
            isPremium: $("#chkisPremiumPaid").is(":checked") ? 1 : 0,
            enableSms: $("#chkEnableSms").is(":checked") ? 1 : 0,
            company: $("#company").val(),
        };

        const url = id ? `/employees/${id}` : `/employees`;
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully', "success");
                    $('#employeeModal').modal('hide');
                    getemployee();
                } else {
                    let msg = response.message || 'Something went wrong';
                    showAlert(msg, 'error');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                showAlert('Some error occurred', 'error');
            }
        });
    });


    //------------------------------------------------------------------------------get employee table
    function getemployee() {
    showSpinner('tableSpinner');
    $('#userTable thead').empty();
    $('#userTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#userTable')) {
        $('#userTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getEmployees',
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
                $('#userTable thead').html(theadHtml);

                // 🔹 Step 3: Build table rows dynamically
                let rows = '';
                data.forEach(user => {
                    rows += '<tr>';
                    keys.forEach(key => {
                        rows += `<td>${user[key] ?? ''}</td>`;
                    });

                    // Add edit/delete icons
                    rows += `
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="edit(${user.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#employeeModal"></i>
                        </td>
                        <td>
                            <i class="bi bi-trash text-danger" 
                               style="cursor:pointer"
                               onclick="deleteRow(${user.id})"></i>
                        </td>
                    `;
                    rows += '</tr>';
                });

                // 🔹 Step 4: Render data
                $('#userTable tbody').html(rows);

                // 🔹 Step 5: Optional DataTable initialization
                if (typeof initializeDataTable === 'function') {
                    initializeDataTable("#userTable", false, [0,1,2,3,4,5,6,7,8]);
                }

            } else {
                // No data fallback
                $('#userTable thead').html('<tr><th>No Data Found</th></tr>');
                $('#userTable tbody').html('');
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