




@extends('layouts.app')

@section('title', 'Manage Companies')

@section('content')

<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#companyModal">New Company</button>

{{-- modal --}}
<x-modal id="companyModal" title="Manage company" size="modal-sm" data-default-oldTitle="Manage company">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            <div class="col-sm-12">
                <label>Company Name</label>
                <input type="text" name="companyName" id="companyName" class="form-control form-control-sm">
            </div>
            <div class="col-sm-12">
                <label>Company Code</label>
                <input type="text" name="companyCode" id="companyCode" class="form-control form-control-sm">
            </div>
        </div>
        <input type="hidden" id="companyId"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnSaveEmpChanges" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>



{{-- table --}}
<div class="table-responsive-sm w-100 mt-2 card p-2">
    <table id="companyTable" class="table table-hover table-sm"  style="font-size: 10pt; border-collapse: collapse; width: 1567px;" cellspacing="0" border="1" rules="all">
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
    getCompanies()
});

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    //delete company
    function deleteCompany(id) {
        showConfirmModal(`/companies/${id}`, function() {
            getCompanies(); // refresh after success
        },`Are you sure you want to delete this company ${id}`);
    }


    // 🟩Edit company
    function editCompany(id) {
        $.ajax({
            url: '/companies/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const company = response.data;

                    // fill modal inputs
                    $("#companyId").val(company.id);
                    $("#companyName").val(company.name);
                    $("#companyCode").val(company.code);

                    // change modal title and button text (optional)
                    $("#companyModal .modal-title").text("Edit Company");
                    $("#btnSaveEmpChanges").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function newCompany() {
    $("#companyId").val('');
    $("#companyName").val('');
    $("#companyCode").val('');
    $("#companyModal .modal-title").text("Add Company");
    $("#btnSaveEmpChanges").text("Save");
    }

    //save
    $("#btnSaveEmpChanges").on("click", function() {
    const id = $("#companyId").val(); // check if editing
    const data = {
        name: $("#companyName").val(),
        code: $("#companyCode").val()
    };

    // Simple validation
    if (!data.name || !data.code) {
       showAlert('Please fill all fields', 'warning');
        return;
    }

    // Determine endpoint and method
    const url = id ? `/companies/${id}` : `/companies`;
    const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully',"success");
                    $('#companyModal').modal('hide');
                    getCompanies(); // reload table
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

    
    function getCompanies() {
    showSpinner('tableSpinner');
    $('#companyTable thead').empty();
    $('#companyTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#companyTable')) {
        $('#companyTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getCompanies',
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
                $('#companyTable thead').html(theadHtml);

                // 🔹 Step 3: Build table rows dynamically
                let rows = '';
                data.forEach(company => {
                    rows += '<tr>';
                    keys.forEach(key => {
                        rows += `<td>${company[key] ?? ''}</td>`;
                    });

                    // Add edit/delete icons
                    rows += `
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="editCompany(${company.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#companyModal"></i>
                        </td>
                        <td>
                            <i class="bi bi-trash text-danger" 
                               style="cursor:pointer"
                               onclick="deleteCompany(${company.id})"></i>
                        </td>
                    `;
                    rows += '</tr>';
                });

                // 🔹 Step 4: Render data
                $('#companyTable tbody').html(rows);

                // 🔹 Step 5: Optional DataTable initialization
                if (typeof initializeDataTable === 'function') {
                    initializeDataTable("#companyTable", true, [0,1,2]);
                }

            } else {
                // No data fallback
                $('#companyTable thead').html('<tr><th>No Data Found</th></tr>');
                $('#companyTable tbody').html('');
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