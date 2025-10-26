




@extends('layouts.app')

@section('title', 'Manage Branches')

@section('content')

<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#branchModal">New Branch</button>

{{-- modal --}}
<x-modal id="branchModal" title="Manage Branch" size="modal-sm" data-default-oldTitle="Manage Branch">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            <div class="col-sm-12">
                <label>branch Name</label>
                <input type="text" name="branchName" id="branchName" class="form-control form-control-sm">
            </div>

            <div class="col-sm-12">
                <label>company</label>
               <select name="company" id="company" class="form-select form-select-sm"></select>
            </div>
        </div>
        <input type="hidden" id="branchId"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnSaveBranch" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>



{{-- table --}}
<div class="table-responsive-sm w-100 mt-2 card p-2">
    <table id="branchTable" class="table table-hover table-sm"  style="font-size: 10pt; border-collapse: collapse; width: 1567px;" cellspacing="0" border="1" rules="all">
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
    getbranches()
    let companydropdown = $("#company")
    fillCompany(companydropdown)
});

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    //delete company
    function deleteBranch(id) {
        showConfirmModal(`/branches/${id}`, function() {
            getbranches(); // refresh after success
        },`Are you sure you want to delete this branch ${id}`);
    }


    // 🟩Edit company
    function editBranch(id) {
        $.ajax({
            url: '/branches/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const branch = response.data;

                    // fill modal inputs
                    $("#branchId").val(branch.id);
                    $("#branchName").val(branch.name);
                    $("#company").val(branch.companyId);
                    // change modal title and button text (optional)
                    $("#branchModal .modal-title").text("Edit Department");
                    $("#btnSaveBranch").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function newBranch() {
    $("#branchId").val('');
    $("#branchName").val('');
    $("#branchModal .modal-title").text("Add branch");
    $("#btnSaveBranch").text("Save");
    }

    //save
    $("#btnSaveBranch").on("click", function() {
    const id = $("#branchId").val(); // check if editing

    const fields = [
            { id: "branchName", label: "Name" },
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
        name: $("#branchName").val(),
        company: $("#company").val()
    };

    // Simple validation
    if (!data.name || !data.company) {
       showAlert('Please fill all fields', 'warning');
        return;
    }

    // Determine endpoint and method
    const url = id ? `/branches/${id}` : `/branches`;
    const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully',"success");
                    $('#branchModal').modal('hide');
                    getbranches(); // reload table
                } else {
                    let msg =  response.message || 'Something went wrong'
                   showAlert(msg, 'error');
                }
            },
            error: function(xhr) {
                msg ="some error occured"
                console.log(Json);
               showAlert(msg, 'error');
            }
        });
    });

    
    function getbranches() {
    showSpinner('tableSpinner');
    $('#branchTable thead').empty();
    $('#branchTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#branchTable')) {
        $('#branchTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getBranches',
        type: 'GET',
        success: function(response) {
            console.log(response);

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
                $('#branchTable thead').html(theadHtml);

                // 🔹 Step 3: Build table rows dynamically
                let rows = '';
                data.forEach(branch => {
                    rows += '<tr>';
                    keys.forEach(key => {
                        rows += `<td>${branch[key] ?? ''}</td>`;
                    });

                    // Add edit/delete icons
                    rows += `
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="editBranch(${branch.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#branchModal"></i>
                        </td>
                        <td>
                            <i class="bi bi-trash text-danger" 
                               style="cursor:pointer"
                               onclick="deleteBranch(${branch.id})"></i>
                        </td>
                    `;
                    rows += '</tr>';
                });

                // 🔹 Step 4: Render data
                $('#branchTable tbody').html(rows);

                // 🔹 Step 5: Optional DataTable initialization
                if (typeof initializeDataTable === 'function') {
                    initializeDataTable("#branchTable", false, [0,1,2,3,4]);
                }

            } else {
                // No data fallback
                $('#branchTable thead').html('<tr><th>No Data Found</th></tr>');
                $('#branchTable tbody').html('');
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