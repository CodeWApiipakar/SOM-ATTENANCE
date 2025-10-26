




@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="profile-tab" data-bs-toggle="tab"  data-bs-target="#Employees" href="#Employees" role="tab"
            aria-controls="Employees" aria-selected="false">Employees</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="home-tab" data-bs-toggle="tab"  data-bs-target="#Devices" href="#Devices" role="tab"
            aria-controls="Devices" aria-selected="true">Devices</a>
    </li>
</ul>


<div class="tab-content" id="myTabContent">

    {{-- employee content --}}
    <div class="tab-pane fade show active mt-3" id="Employees" role="tabpanel" aria-labelledby="home-tab">
        <p>Employees</p>
    </div>

    {{-- device content --}}
    <div class="tab-pane fade  mt-3" id="Devices" role="tabpanel" aria-labelledby="contact-tab">
    </div>
</div>
@endsection

@push("scripts")
<script>
    var activeTabId = localStorage.getItem('activeTabRegister1');
      
      if (activeTabId) {
          //$('#myTab a[href="#' + activeTabId + '"]').tab('show');
          var tab = new bootstrap.Tab(document.querySelector('#myTab a[href="#' + activeTabId + '"]'));
          tab.show();
      }

      //// Store active tab ID when a tab is shown
      $('#myTab  a').on('shown.bs.tab', function (e) {
          var newTabId = $(e.target).attr('href').substr(1);
          if (localStorage.getItem('activeTabRegister1') !== newTabId) {
              localStorage.setItem('activeTabRegister1', newTabId);
          }
      });
</script>
@endpush