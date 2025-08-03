<!--begin::Sidebar-->
<aside id="aside-bar" class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <!--begin::Brand Link-->
{{--        <a href="{{route('admin.dashboard')}}" class="brand-link">--}}
            <!--begin::Brand Image-->
            <img
                src="{{ asset('assets/img/AdminLTELogo.png') }}"
                alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Hospital</span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->
    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="menu"
                data-accordion="false"
            >
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>داشبورد</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-tree-fill"></i>
                        <p>
                            اطلاعات پایه
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" role="navigation" aria-label="Navigation 7">
                        <li class="nav-item" >
                            <a class="nav-link" href="{{ route('roles.index') }}">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>مدیریت نقش ها و مجوز ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.index') }}">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>مدیریت ادمین‌ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/ui/timeline">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Timeline</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
