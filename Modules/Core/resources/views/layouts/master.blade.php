<!doctype html>
<html lang="en" dir="rtl">
<!--begin::Head-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>بلاگ لاراول - @yield('title')</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="title" content="AdminLTE 4 | Layout RTL"/>
    <meta name="author" content="ColorlibHQ"/>
    <meta
            name="description"
            content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
            name="keywords"
            content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
            integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
            crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
            integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
            crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
            integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
            crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('css/adminlte.rtl.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/plugins/persian-datepicker/css/persianDatepicker-default.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/self.css') }}"/>

    <!--end::Required Plugin(AdminLTE)-->

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
          rel="stylesheet"/>

    <style>
        /* RTL fixes for Select2 */
        .select2-container--bootstrap-5 {
            text-align: right;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-direction: row-reverse;
            flex-wrap: wrap-reverse;
            padding-right: 5px;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            margin-right: 0;
            margin-left: 5px;
            direction: rtl;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            text-align: right;
            padding-right: 6px;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            margin-left: 2px;
            margin-right: auto;
        }
    </style>

    @yield('styles')
</head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
<div class="app-wrapper">

    @include('core::layouts._header')

    @include('core::layouts._sidebar')

    <!--begin::App Main-->
    <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Row-->
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"></h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            {{--                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">داشبورد</a></li>--}}
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
                <!--end::Row-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
            <!--begin::Container-->
            <div class="container-fluid">

                @include('core::includes._notifications')
                @include('core::includes._errors')

                @yield('content')
            </div>
            <!--end::Container-->
        </div>
        <!--end::App Content-->
    </main>
    <!--end::App Main-->

    @include('core::layouts._footer')
</div>
<!--end::App Wrapper-->
<!--begin::Script-->
<!--begin::Third Party Plugin(OverlayScrollbars)-->
<script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
        crossorigin="anonymous"
></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"
></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"
></script>
<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
<script src="{{ asset('js/adminlte.js') }}"></script>
<!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
<script src="{{ asset('js/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/plugins/persian-datepicker/js/jquery-1.10.1.min.js') }}"></script>
<script src="{{ asset('assets/plugins/persian-datepicker/js/persianDatepicker.min.js') }}"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fa.js"></script>

<script src="{{ asset('js/main.js') }}"></script>
<script>
    // Initialize OverlayScrollbars
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }

        // Initialize Select2 with RTL and Persian language
        $('.select2').select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            language: 'fa',
            placeholder: "لطفا انتخاب کنید",
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true,
            closeOnSelect: false
        });

        // Fix for Bootstrap 5 modal focus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    });
</script>
<!-- CKEditor (local copy) -->
<script src="{{ asset('js/ckeditor.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ckeditor').forEach((editorEl) => {
            ClassicEditor
                .create(editorEl, {
                    language: 'fa' // Persian language support
                })
                .catch(error => {
                    console.error(error);
                });
        });
    });
</script>



@yield('scripts')

<!--end::OverlayScrollbars Configure-->
<!--end::Script-->
</body>
<!--end::Body-->
</html>
