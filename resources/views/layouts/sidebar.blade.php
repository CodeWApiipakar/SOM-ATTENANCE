
<div id="sidebar">
<div class="sidebar-wrapper active">


    <div class="d-flex justify-content-between align-items-center m-1">
        <div class="logo">
            <img src="{{ asset('mazer/images/som.PNG') }}" class="mt-3 ms-3" width="100" alt="Logo">
        </div>
        <div class="theme-toggle d-flex gap-2  align-items-center mt-2 me-2">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                role="img" class="iconify iconify--system-uicons" width="20" height="20"
                preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path
                        d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                        opacity=".3"></path>
                    <g transform="translate(-210 -1)">
                        <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                        <circle cx="220.5" cy="11.5" r="4"></circle>
                        <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                    </g>
                </g>
            </svg>
            <div class="form-check form-switch fs-6">
                <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                <label class="form-check-label"></label>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                </path>
            </svg>
        </div>
    </div>
    <div class="sidebar-menu">
        <ul class="menu px-1">
            <li class="sidebar-title">Menu List</li>

            {{-- Dashboard --}}
            <li class="sidebar-item">
                <a href="/" class="sidebar-link">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Admin (only for superusers) --}}
            
            @if(session('isAdmin') == 1)
            <li class="sidebar-item has-sub">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-gear"></i>
                    <span>Admin</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="/companies" class="submenu-link">Manage Companies</a></li>
                    <li class="submenu-item"><a href="/users" class="submenu-link">Manage Users</a></li>
                    <li class="submenu-item"><a href="/employeeData" class="submenu-link">Manage Data</a></li>
                    <li class="submenu-item"><a href="/reports/manage-report" class="submenu-link">Manage Reports</a></li>
                </ul>
            </li>
            @endif

            {{-- Personal --}}
            <li class="sidebar-item has-sub">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-person-fill"></i>
                    <span>Personal</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="/employees" class="submenu-link">Employees</a></li>
                    <li class="submenu-item"><a href="/branches" class="submenu-link">Branches</a></li>
                    <li class="submenu-item"><a href="/departments" class="submenu-link">Department</a></li>
                    <li class="submenu-item"><a href="/sections" class="submenu-link">Section</a></li>
                    <li class="submenu-item"><a href="/devices" class="submenu-link">Device</a></li>
                    <li class="submenu-item"><a href="/leaves" class="submenu-link">Leaves</a></li>
                    <li class="submenu-item"><a href="/overtime" class="submenu-link">Overtime</a></li>
                </ul>
            </li>

            {{-- Attendance --}}
            <li class="sidebar-item has-sub">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-fingerprint"></i>
                    <span>Attendance</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="/shift" class="submenu-link">Shift</a></li>
                    <li class="submenu-item"><a href="/timetable" class="submenu-link">TimeTable</a></li>
                    <li class="submenu-item"><a href="/schedule" class="submenu-link">Employee Schedule</a></li>
                    <li class="submenu-item"><a href="/manual-punch" class="submenu-link">Manual Punch Entry</a></li>
                    <li class="submenu-item"><a href="/error-adjustment" class="submenu-link">Error Adjustment</a></li>
                </ul>
            </li>

            {{-- Reports --}}
            <li class="sidebar-item has-sub">
                <a href="#" class="sidebar-link">
                    <i class="bi bi-receipt"></i>
                    <span>Reports</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="/reports/summary-report" class="submenu-link">Generation Report</a></li>
                    <li class="submenu-item"><a href="/reports/summary-report" class="submenu-link">Summary Report</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
</div>
@push('scripts')
<script>
        
//     var currentPath = window.location.pathname;
   
    
// $(".sidebar-menu .menu .sidebar-item").each(function () {
//     if ($(this).hasClass("has-sub")) {
//         // Iterate through the submenu links inside the has-sub item
//         $(this).find(".submenu .submenu-item .submenu-link").each(function () {
//             var subLinkPath = $(this).attr('href');
//             if (subLinkPath === currentPath) {
//                 console.log(currentPath);
//                 // Add active class to the parent sidebar-item
//                 $(this).closest('.sidebar-item').addClass('active');
//                 $(this).prop("style", "font-weight: bold;color:darkblue;");
//             }
//         });
//     } else {
//         // Iterate over each sidebar link
//         $(this).find('.sidebar-link').each(function () {
//             var linkPath = $(this).attr('href');
//             if (linkPath === currentPath) {
//                 // Add active class to the closest sidebar-item
//                 $(this).closest('.sidebar-item').addClass('active');
//             }
//         });
//     }
// });


$(document).ready(function () {
        var currentPath = window.location.pathname;

        $(".sidebar-menu .menu .sidebar-item").each(function () {
            var $sidebarItem = $(this);

            if ($sidebarItem.hasClass("has-sub")) {
                // Check submenu links inside the has-sub item
                $sidebarItem.find(".submenu .submenu-item .submenu-link").each(function () {
                    var subLinkPath = $(this).attr('href');

                    if (subLinkPath === currentPath) {
                        // Add active class to parent and style the active submenu item
                        $sidebarItem.addClass('active');  // Highlight parent
                        $(this).css({ "font-weight": "bold", "color": "darkblue" });
                        $sidebarItem.find('.submenu').show(); // Ensure submenu is visible
                    }
                });
            } else {
                // Check direct sidebar links
                $sidebarItem.find('.sidebar-link').each(function () {
                    var linkPath = $(this).attr('href');

                    if (linkPath === currentPath) {
                        // Add active class to the parent sidebar-item
                        $sidebarItem.addClass('active');
                    }
                });
            }
        });
    });
</script>
    
@endpush
