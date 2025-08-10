<!--begin::Sidebar-->
<aside id="aside-bar" class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <!--begin::Brand Link-->
                <a href="{{route('admin.dashboard')}}" class="brand-link">
        <!--begin::Brand Image-->
{{--        <img--}}
{{--            src="{{ asset('assets/img/AdminLTELogo.png') }}"--}}
{{--            alt="AdminLTE Logo"--}}
{{--            class="brand-image opacity-75 shadow"--}}
{{--        />--}}
        <!--end::Brand Image-->
        <!--begin::Brand Text-->
        <span class="brand-text fw-light">Admin panel</span>
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
                        <i class="nav-icon bi bi-info-square"></i>
                        <p>
                            اطلاعات پایه
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" role="navigation" aria-label="Navigation 7">
                        <li class="nav-item" >
                            <a class="nav-link" href="{{ route('roles.index') }}">
                                <i class="nav-icon bi bi-shield-lock"></i>
                                <p>مدیریت نقش ها و مجوز ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.index') }}">
                                <i class="nav-icon bi bi-person-square"></i>
                                <p>مدیریت ادمین‌ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-tree-fill"></i>
                                <p>
                                    مناطق
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" role="navigation" aria-label="Navigation 7">
                                <li class="nav-item" >
                                    <a class="nav-link" href="{{ route('cities.index') }}">
                                        <i class="nav-icon bi bi-geo"></i>
                                        <p>شهرها</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="nav-icon bi bi-pin-map"></i>
                                        <p>استان‌ها</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-boxes"></i>
                        <p>
                            مدیریت محصولات
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview" role="navigation" aria-label="Products Navigation">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('categories.index') }}">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>دسته‌بندی‌ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('brands.index') }}">
                                <i class="nav-icon bi bi-tags"></i>
                                <p>برندها</p>
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
