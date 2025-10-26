




@extends('layouts.app')

@section('title', 'Users')

@section('content')
<button type="button" class="btn btn-primary block btn-sm" data-bs-toggle="modal" data-bs-target="#usersModal">New Users</button>

{{-- modal --}}
<x-modal id="usersModal" title="Manage user" size="modal-lg" data-default-oldTitle="Manage user">
    <x-slot:body>
        <div class="row mb-3">

            <div class="col-sm-4">
                <label>company</label>
               <select name="company" id="company" class="form-select form-select-sm"></select>
            </div>

            <div class="col-sm-4">
                <label>Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-sm">
            </div>

            <div class="col-sm-4">
                <label>Username</label>
                <input type="text" name="username" id="username" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-4">
                <label>Email</label>
                <input type="text" name="Email" id="Email" class="form-control form-control-sm">
            </div>

            <div class="col-sm-4">
                <label>Phone</label>
                <input type="tel" name="phone" id="phone" class="form-control form-control-sm">
            </div>

            <div class="col-sm-4">
            <div class="form-check">
                <input type="checkbox" name="chkIsAdmin" id="chkIsAdmin">
                <label>isAdmin</label>
            </div>
            </div>
        </div>

        <div class="row">
             <div class="col-sm-12 d-flex gap-3">
              

                <div class="form-check">
                    <input type="checkbox" checked name="chkisAllowed" id="chkisAllowed">
                    <label>isAllowed</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="chkisPremiumPaid" id="chkisPremiumPaid">
                    <label>isPremium Paid</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="chkEnableSms" id="chkEnableSms">
                    <label>Enable Sms</label>
                </div>
            </div>
        </div>


        <input type="hidden" id="userid"> <!-- holds edit id -->
    </x-slot:body>

    <x-slot:footer>
        <button id="btnUserChanges" class="btn btn-primary btn-sm">Save</button>
    </x-slot:footer>
</x-modal>


{{-- subscription modal --}}
<x-modal id="subscriptionModal" title="Manage Subscription" size="modal-md" data-default-oldTitle="Manage Subscription">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            <div class="col-12">
               <label>Expire Date</label>
                <input type="date" id="expireDate" name="expireDate" class="form-control form-control-sm" />
            </div>
            <div class="col-12">
                <div class="form-check m-0 p-0">
                    <input type="checkbox" name="chkIsPremium" id="chkIsPremium">
                    <label>IsPremium</label>
                </div>
            </div>
        </div>
        <input type="hidden" id="subscriptionUserId"> <!-- holds edit id -->
    </x-slot:body>
    <x-slot:footer>
        <button id="btnBuySubscription" class="btn btn-success btn-sm">Buy Subscription</button>
    </x-slot:footer>
</x-modal>


{{-- reset password modal --}}
<x-modal id="resetPassModal" title="Change Password" size="modal-md" data-default-oldTitle="Change Password">
    <x-slot:body>
        <div class="row mb-3 gap-2">
            <div class="col-12 position-relative">
              <label>New Password</label>
                <input type="password" id="txtNewPassword" class="form-control form-control-sm mb-3 pr-5" />
                <span class="toggle-password" onclick="togglePassword2()" style="position: absolute; top: 50%; right: 8%; transform: translateY(-50%); cursor: pointer;">
                    <i class="bi bi-eye-slash-fill text-secondary" id="eyeIcon2"></i>
                </span>
            </div>
        </div>
        <input type="hidden" id="resetPassUserId"> <!-- holds edit id -->
    </x-slot:body>
    <x-slot:footer>
        <button id="btnResetPassword" class="btn btn-danger btn-sm">Reset</button>
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
     $(document).ready(() => {
    getUsers()
    let companydropdown = $("#company")
    fillCompany(companydropdown)
});

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
    //------------------ toggle password visibility with auto-hide ------------------------
    let timeoutHandle2; // declare globally

    function togglePassword2() {
        const passwordInput = document.getElementById('txtNewPassword');
        const eyeIcon = document.getElementById('eyeIcon2');

        if (passwordInput.type === "password") {
            // Show password
            passwordInput.type = "text";
            eyeIcon.classList.remove('bi-eye-slash-fill');
            eyeIcon.classList.add('bi-eye-fill');

            // Auto-hide after 3 seconds
            clearTimeout(timeoutHandle2); 
            timeoutHandle2 = setTimeout(() => {
                passwordInput.type = "password";
                eyeIcon.classList.remove('bi-eye-fill');
                eyeIcon.classList.add('bi-eye-slash-fill');
            }, 3000);
        } else {
            // Hide manually
            passwordInput.type = "password";
            eyeIcon.classList.remove('bi-eye-fill');
            eyeIcon.classList.add('bi-eye-slash-fill');
            clearTimeout(timeoutHandle2);
        }
    }


    //------------------------------------------- delete user -------------------------------
    function deleteRow(id) {
        showConfirmModal(`/user/${id}`, function() {
            getUsers(); // refresh after success
        },`Are you sure you want to delete this user ID = ${id}`);
    }


    //------------------------------------------- change subscription -------------------------------
    function BuySubscription(id,expiredate) {
        $("#subscriptionModal").modal("show")
        $("#subscriptionUserId").val(id);
        const formattedDate = expiredate.split(' ')[0]; // "2026-01-25"
        $("#expireDate").val(formattedDate);
    }


    //------------------------------------------- reset user subscription -------------------------------
    function ResetPassword(id) {
         $("#resetPassModal").modal("show")
            $("#resetPassUserId").val(id);
    }

    
    //-------------------------------------------- Buy subsctiption button --------------------------------
    $("#btnBuySubscription").on("click", function() {
        const id = $("#subscriptionUserId").val();
        const data = {
            expireDate: $("#expireDate").val(),
            isPremium: $("#chkIsPremium").is(":checked") ? 1 : 0,
        };

        $.ajax({
            url: '/user/update-expire/'+id,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(response.message, "success");
                    $('#subscriptionModal').modal('hide');
                    getUsers();
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
    
    //-------------------------------------------- reset password button --------------------------------
    $("#btnResetPassword").on("click", function() {
        const id = $("#resetPassUserId").val();
        const data = {
            newPassword: $("#txtNewPassword").val(),
        };

        $.ajax({
            url: '/user/change-password/'+id,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(response.message, "success");
                    $('#resetPassModal').modal('hide');
                    getUsers();
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

    //-------------------------------------------- 🟩Edit user --------------------------------
    function edit(id) {
        $.ajax({
            url: '/user/' + id,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    const user = response.data;

                    // fill modal inputs
                    $("#company").val(user.id);
                    $("#userid").val(user.id);
                    $("#name").val(user.name);
                    $("#Email").val(user.name);
                    $("#username").val(user.name);
                    $("#phone").val(user.name);
                    $("#chkIsAdmin").prop("checked", user.isAdmin == 1);
                    $("#chkisAllowed").prop("checked", user.isAllowed == 1);
                    $("#chkisPremiumPaid").prop("checked", user.isPremium == 1);
                    $("#chkEnableSms").prop("checked", user.enableSms == 1);

                    // change modal title and button text (optional)
                    $("#usersModal .modal-title").text("Edit user");
                    $("#btnUserChanges").text("Update");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }
 
    function newUser() {
    $("#userid").val('');
    $("#name").val('');
    $("#username").val('');
    $("#email").val('');
    $("#phone").val('');
    $("#username").val('');
    $("#usersModal .modal-title").text("Add user");
    $("#btnUserChanges").text("Save");
    }

    //-------------------------------------------- save & update ------------------------------------------
    $("#btnUserChanges").on("click", function() {
        const id = $("#userid").val();
        const fields = [
            { id: "username", label: "Username" },
            { id: "name", label: "Name" },
            { id: "Email", label: "Email" },
            { id: "phone", label: "Phone" },
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

        const url = id ? `/user/${id}` : `/user`;
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function(response) {
                if (response.status === 200) {
                    showAlert(id ? 'Updated Successfully' : 'Saved Successfully', "success");
                    $('#usersModal').modal('hide');
                    getUsers();
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


    //------------------------------------------------------------------------------get users table
    function getUsers() {
    showSpinner('tableSpinner');
    $('#userTable thead').empty();
    $('#userTable tbody').empty();
     // ✅ Destroy previous DataTable if exists
    if ($.fn.DataTable.isDataTable('#userTable')) {
        $('#userTable').DataTable().destroy();
    }
    $.ajax({
        url: '/getUsers',
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
                theadHtml += '<th>Change Pass</th><th>Subscription</th><th>Edit</th><th>Delete</th></tr>';
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
                            <i class="bi bi-key text-danger bg-danger bg-opacity-25 px-3 py-1 rounded" 
                            style="cursor:pointer"
                            onclick="ResetPassword(${user.id})"></i>
                        </td>
                        <td>
                            <i class="bi bi-coin text-primary bg-success bg-opacity-25 px-3 py-1 rounded" 
                            style="cursor:pointer"
                            onclick="BuySubscription(${user.id},'${user.expireDate}')"></i>
                        </td>
                        <td>
                            <i class="bi bi-pencil-square text-success" 
                               style="cursor:pointer"
                               onclick="edit(${user.id})"
                               data-bs-toggle="modal"
                               data-bs-target="#usersModal"></i>
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