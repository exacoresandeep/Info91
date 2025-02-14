  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
      <img src="{{asset('cs/assets/Logo.svg')}}" alt="info91 Logo" width="95" style="">
      <span class="brand-text font-weight-light">| Admin Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar mb-5">
      <!-- Sidebar user panel (optional) -->
      

      <!-- SidebarSearch Form -->
      <div class="form-inline mt-1">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
         
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                General Management
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="states" class="nav-link">
                  <i class="fas fa-globe nav-icon"></i>
                  <p>States</p>
                </a>
              </li>
              {{-- <li class="nav-item">
                <a href="regions" class="nav-link">
                  <i class="fas fa-road nav-icon"></i>
                  <p>Regions</p>
                </a>
              </li> --}}
              <li class="nav-item">
                <a href="districts" class="nav-link">
                  <i class="fas fa-map-marker nav-icon"></i>
                  <p>Districts</p>
                </a>
              </li>
              {{-- <li class="nav-item">
                <a href="taluks" class="nav-link">
                  <i class="fas fa-map-signs nav-icon"></i>
                  <p>Taluks</p>
                </a>
              </li> --}}
              <li class="nav-item">
                <a href="pincode" class="nav-link">
                  <i class="fas fa-map-pin nav-icon"></i>
                  <p>Pincode</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Group Management
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="group-approvals" class="nav-link">
                  <i class="fas fa-dot-circle nav-icon"></i>
                  <p>Group Approvals</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="approved-groups" class="nav-link">
                  <i class="fas fa-check-square nav-icon"></i>
                  <p>Approved Groups</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="rejected-groups" class="nav-link">
                  <i class="fas fa-minus-square nav-icon"></i>
                  <p>Rejected Groups</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="plan" class="nav-link">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>
                Plans
                {{-- <span class="badge badge-info right">2</span> --}}
              </p>
            </a>
          </li> 
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Categories
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="first-category" class="nav-link">
                  <i class="fas fa-list nav-icon"></i>
                  <p>Category 1</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="second-category" class="nav-link">
                  <i class="fas fa-server nav-icon"></i>
                  <p>Category 2</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="third-category" class="nav-link">
                  <i class="fas fa-indent nav-icon"></i>
                  <p>Category 3</p>
                </a>
              </li>
             
            </ul>
          </li>
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>


  
  <!--find icons from following link
   https://adminlte.io/themes/AdminLTE/pages/UI/icons.html
   
   made by sanchithira-->