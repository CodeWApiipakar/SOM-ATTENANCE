<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'App')</title>
    <link rel="icon" type="favicon" href="{{ asset('mazer/images/favIcon.PNG') }}" />
    {{-- Global CSS --}}
    <link rel="stylesheet" href="{{ asset('mazer/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/choices.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/extra-component-sweetalert.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/datatable.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/css/iconly.css') }}">

    
    <script src="{{ asset('mazer/js/initTheme.js') }}"></script>
    <script src="{{ asset('mazer/js/jquery.js') }}"></script>
    <script src="{{ asset('mazer/js/echarts.min.js') }}"></script>
    <script src="{{ asset('mazer/js/bootstrap.bundle.min.js') }}"></script>
    {{-- <script src="{{ asset('mazer/js/select2.js') }}"></script> --}}
    <script src="{{ asset('mazer/DataTable/DataTables.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/buttons.dataTables.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/buttons.bootstrap5.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/jszip.min.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/pdfmake.min.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/vfs_fonts.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/buttons.print.min.js') }}"></script>
    <script src="{{ asset('mazer/DataTable/vfs_fonts.js') }}"></script>
    <script src="{{ asset('mazer/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('mazer/js/choices.js') }}"></script>
    
    <style>


        body {
            overflow: hidden
        }

        #main_content_section {
            /*height: 770px;*/
            height: calc(100vh - 160px);
            scroll-behavior: smooth;
            overflow-y: auto;
            overflow-x: hidden;
            box-sizing: border-box;
        }

            /* Style the scrollbar for WebKit-based browsers */
            #main_content_section::-webkit-scrollbar {
                width: 6px; /* Set the width of the scrollbar */
            }

            #main_content_section::-webkit-scrollbar-track {
                background: #f0f0f0; Optional: Background color of the scrollbar track
            }

            #main_content_section::-webkit-scrollbar-thumb {
                background: #888; /* Optional: Color of the scrollbar thumb */
                border-radius: 10px; /* Optional: Round the corners of the scrollbar thumb */
            }

                #main_content_section::-webkit-scrollbar-thumb:hover {
                    background: #555; /* Optional: Darken the scrollbar thumb when hovered */
                }

        .choices {
        max-width: 100%; /* Adjust the width */
        font-size: 12px; /* Adjust the text size */
    }

    .choices__inner {
        min-height: 30px; /* Adjust the height */
        padding: 5px 5px; /* Reduce padding */
    }

    .choices__list--single {
        padding: 0;
    }

    .choices[data-type*="select-one"] .choices__inner {
        padding: 3px 5px;
    }

    .choices__list--dropdown {
        position: absolute !important; /* FIXED TO ABSOLUTE */
        z-index: 1090 !important;
        background-color: white;
        font-size: 12px; /* readable size */
        border: 1px solid #ccc;
        border-radius: 0.25rem;
    }

    .choices__list--dropdown .choices__item {
        padding: 5px 5px; /* Adjust dropdown padding */
    }
    </style>

    @stack('styles')
</head>
<body>

    <div id="app">
        {{-- Sidebar --}}
        @include('layouts.sidebar')
        {{-- Main Content --}}
        <div id="main">
           <div>
            @include('layouts.header')
           </div>
            <div class="my-3 p-4" id="main_content_section">
                @yield('content')
            </div>
        </div>
    </div>

    <x-deleteModal 
                id="confirmDeleteModal" 
                title="Confirm Delete" 
                message="Are you sure you want to delete this record?" 
                buttonText="Delete" 
                color="danger" 
                 />
    
    
    {{-- Global Scripts --}}
  

    <script>
         
         const Swal2 = Swal.mixin({
        customClass: {
            input: 'form-control',
            popup: 'small-swal-popup', // Custom class for additional styling
            title: 'h6',               // Bootstrap class for smaller title
            content: 'text-muted'      // Bootstrap class for muted text
        }
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            popup: 'small-toast-popup' // Custom class for small toast
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });


    //sweet alert initializer
    function showAlert(text, type) {
        Toast.fire({ icon: type, title: text });
    }

    //show loader and hide
   
    window.showSpinner = function(id) {
    $('#' + id).fadeIn(100);
    }

    window.hideSpinner = function(id) {
        $('#' + id).fadeOut(100);
    }

    // function showLoader() {
    //     $('#globalLoader').fadeIn(200);
    // }

    // function hideLoader() {
    //     $('#globalLoader').fadeOut(200);
    // }

    //datatable initializer
    function initializeDataTable(tblId, exportButtonsEnabled, exportColumns) {
        // Use jQuery to get the GridView client ID
        var tableId = $(tblId);

        // Destroy any existing DataTable instance
        if ($.fn.DataTable.isDataTable(tableId)) {
            tableId.DataTable().destroy();
        }

        // Prepare DataTable options
        var dataTableOptions = {
            pageLength: 10,
            fixedHeader: true,
            responsive: true,
            ordering: false,
            order: [[2, 'desc']],
        };

        // Add export buttons if enabled
        if (exportButtonsEnabled) {
            dataTableOptions.dom = 'Bfrtip';
            dataTableOptions.buttons = [
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'btn btn-primary px-3 btn-sm',
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'btn btn-primary px-3 btn-sm ms-1',
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
                {
                    extend: 'print',
                    text: 'Print',
                    className: 'btn btn-primary px-3 btn-sm ms-1',
                    exportOptions: {
                        columns: exportColumns,
                    },
                },
            ];
        }

        // Initialize DataTable
        tableId.DataTable(dataTableOptions);
    }


    //multichoise initializeer
    function initializeChoices(selector) {
        const element = document.querySelector(selector);
        return new Choices(element, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            position: 'bottom',
        });
    }


    let confirmUrl = null;
    let confirmCallback = null;
    function showConfirmModal(url, callback = null, message = null) {
        confirmUrl = url;
        confirmCallback = callback;

        if (message) {
            $('#confirmDeleteModal .modal-body p').text(message);
        }

        $('#confirmDeleteModal').modal('show');
    }

    $('#btnConfirmDelete').on('click', function() {
        if (!confirmUrl) return;

        $.ajax({
            url: confirmUrl,
            type: 'DELETE',
            success: function(response) {
                $('#confirmDeleteModal').modal('hide');
                let msg = response.message || 'Deleted successfully.'
                showAlert(msg,"success");
                if (typeof confirmCallback === 'function') confirmCallback(response);
            },
            error: function(xhr) {
                $('#confirmDeleteModal').modal('hide');
                const res = xhr.responseJSON;
                let msg = res?.message || 'Error occurred while deleting.'
                showAlert(msg,"error");
                console.error(res);
            }
        });
    });


    $(document).on('hidden.bs.modal', '.modal', function () {
    const modal = $(this);

    // 1️⃣ Clear text, number, email, password inputs
    modal.find('input[type="text"], input[type="number"], input[type="email"], input[type="password"]').val('');

    // 2️⃣ Clear textareas and selects
    modal.find('textarea').val('');
    modal.find('select').prop('selectedIndex', 0);

    // 3️⃣ Clear hidden inputs
    modal.find('input[type="hidden"]').val('');

    // 4️⃣ Reset titles to their original default (if defined)
    const defaultTitle = modal.attr('data-default-oldTitle');
    if (defaultTitle) {
        modal.find('.modal-title').text(defaultTitle);
    }
    });

    // Generic function to handle dependent dropdowns
    function handleDropdownChange(parentSelector, childSelector, fillFunction) {
        $(document).on("change", parentSelector, function () {
            let parentValue = $(this).val();
            let childDropdown = $(childSelector);

            // Optional debug
            console.log(`Changed: ${parentSelector} = ${parentValue}`);

            // Call your custom function
            if (typeof fillFunction === "function") {
                fillFunction(childDropdown, parentValue);
            }
        });
    }

    </script>

    <script src="{{ asset('mazer/js/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('mazer/js/dark.js') }}"></script>
    <script src="{{ asset('mazer/js/app.js') }}"></script>
    <script src="{{ asset('mazer/js/helpers.js') }}"></script>
    @stack('scripts')
</body>
</html>
