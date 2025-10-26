

function fillCompany(companyDropdown) {
    $.ajax({
        url: '/getCompanies',
        type: 'GET',
        success: function(response) {
            console.log(response);

            if (response.status === 200 && response.data.length > 0) {
                const data = response.data;
                let options = '<option value="">Select Company</option>'; // default option

                data.forEach(company => {
                    options += `<option value="${company.id}">${company.name}</option>`;
                });
                // Fill the dropdown
                $(companyDropdown).html(options);
            } else {
                $(companyDropdown).html('<option value="">No companies found</option>');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $(companyDropdown).html('<option value="">Error loading companies</option>');
        },
    });
}


function fillBranch(branchDropdown,companyId) {
    $.ajax({
        url: companyId == "%" ?'/getBranches' :  '/branchesByCompany/'+ companyId,
        type: 'GET',
        success: function(response) {
            if (response.status === 200) {
                const data = response.data;

                if (data.length > 0) {
                    let options = '<option value="">Select branch</option>';
                    data.forEach(branch => {
                        options += `<option value="${branch.id}">${branch.name}</option>`;
                    });
                    $(branchDropdown).html(options);
                } else {
                    // No branches found for this company
                    $(branchDropdown).html('<option value="">No branch found for this company</option>');
                }
            } else {
                // No company found or invalid response
                $(branchDropdown).html('<option value="">No company found</option>');
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $(branchDropdown).html('<option value="">Error loading branch</option>');
        },
    });
}


function fillDepartment(departmentDropdown, companyId) {
    $.ajax({
        url:  companyId == "%" ?'/getdepartments' :  '/departmentByCompany/' + companyId,
        type: 'GET',
        success: function (response) {
            console.log(response);

            if (response.status === 200) {
                const data = response.data;

                if (data.length > 0) {
                    let options = '<option value="">Select Department</option>';
                    data.forEach(department => {
                        options += `<option value="${department.id}">${department.name}</option>`;
                    });
                    $(departmentDropdown).html(options);
                } else {
                    // No department found for this company
                    $(departmentDropdown).html('<option value="">No department found for this company</option>');
                }
            } else {
                // Invalid response or no company found
                $(departmentDropdown).html('<option value="">No company found</option>');
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            $(departmentDropdown).html('<option value="">Error loading department</option>');
        },
    });
}



function fillSection(sectionDropdown, departmentId) {
    $.ajax({
        url:  departmentId == "%" ?'/getdepartments' :  '/departmentByCompany/' + departmentId,
        type: 'GET',
        success: function (response) {
            console.log(response);

            if (response.status === 200) {
                const data = response.data;

                if (data.length > 0) {
                    let options = '<option value="">Select Department</option>';
                    data.forEach(section => {
                        options += `<option value="${section.id}">${section.name}</option>`;
                    });
                    $(sectionDropdown).html(options);
                } else {
                    // No section found for this company
                    $(sectionDropdown).html('<option value="">No section found for this department</option>');
                }
            } else {
                // Invalid response or no department found
                $(sectionDropdown).html('<option value="">No department found</option>');
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            $(sectionDropdown).html('<option value="">Error loading section</option>');
        },
    });
}