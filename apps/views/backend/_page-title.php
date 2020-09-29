<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4  subheader-solid " id="kt_subheader">
    <div class=" container-fluid  d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Mobile Toggle-->
            <button class="burger-icon burger-icon-left mr-4 d-inline-block d-lg-none"
                id="kt_subheader_mobile_toggle">
                <span></span>
            </button>
            <!--end::Mobile Toggle-->
            
            <!--begin::Page Title-->
            <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">
            <?php echo str_replace('&mdash; ' . get('signature'), '', Html::get_title());?> 
            <i class="flaticon2-correct text-success icon-md ml-2"></i>                          
            </h5>
            <!--end::Page Title-->

            <!--begin::Action-->
            <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200"></div>

            <?php 
            foreach ($this->events->apply_filters('toolbar_menu', []) as $namespace) {
                Menu::add_toolbar_menu($namespace);
            }; 
            Menu::load_toolbar_menu();
            ?>
        </div>
        <!--end::Info-->

        <!--begin::Toolbar-->
        <div class="d-flex align-items-center mt-2">
            <!--begin::Daterange-->
            <a href="#" class="btn btn-light btn-sm font-weight-bold mr-2" id="kt_dashboard_daterangepicker"
                data-toggle="tooltip" title="date" data-placement="left">
                <span class="text-muted font-weight-bold mr-2" id="kt_dashboard_daterangepicker_title">Today</span>
                <span class="text-primary font-weight-bold" id="kt_dashboard_daterangepicker_date">Aug 16</span>
                <span class="svg-icon svg-icon-sm svg-icon-primary ml-1">
                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Chat-check.svg--><svg
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                        height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24" />
                            <path
                                d="M4.875,20.75 C4.63541667,20.75 4.39583333,20.6541667 4.20416667,20.4625 L2.2875,18.5458333 C1.90416667,18.1625 1.90416667,17.5875 2.2875,17.2041667 C2.67083333,16.8208333 3.29375,16.8208333 3.62916667,17.2041667 L4.875,18.45 L8.0375,15.2875 C8.42083333,14.9041667 8.99583333,14.9041667 9.37916667,15.2875 C9.7625,15.6708333 9.7625,16.2458333 9.37916667,16.6291667 L5.54583333,20.4625 C5.35416667,20.6541667 5.11458333,20.75 4.875,20.75 Z"
                                fill="#000000" fill-rule="nonzero" opacity="0.3" />
                            <path
                                d="M2,11.8650466 L2,6 C2,4.34314575 3.34314575,3 5,3 L19,3 C20.6568542,3 22,4.34314575 22,6 L22,15 C22,15.0032706 21.9999948,15.0065399 21.9999843,15.009808 L22.0249378,15 L22.0249378,19.5857864 C22.0249378,20.1380712 21.5772226,20.5857864 21.0249378,20.5857864 C20.7597213,20.5857864 20.5053674,20.4804296 20.317831,20.2928932 L18.0249378,18 L12.9835977,18 C12.7263047,14.0909841 9.47412135,11 5.5,11 C4.23590829,11 3.04485894,11.3127315 2,11.8650466 Z M6,7 C5.44771525,7 5,7.44771525 5,8 C5,8.55228475 5.44771525,9 6,9 L15,9 C15.5522847,9 16,8.55228475 16,8 C16,7.44771525 15.5522847,7 15,7 L6,7 Z"
                                fill="#000000" />
                        </g>
                    </svg>
                    <!--end::Svg Icon--></span> </a>
            <!--end::Daterange-->

            <!--begin::Actions-->
            <a href="#" class="btn btn-light btn-hover-primary btn-sm btn-icon mr-2">
                <i class="flaticon2-file text-muted"></i>
            </a>
            <a href="#" class="btn btn-light btn-hover-primary btn-sm btn-icon mr-2">
                <i class="flaticon-download-1 text-muted"></i>
            </a>
            <a href="#" class="btn btn-light btn-hover-primary btn-sm btn-icon mr-2">
                <i class="flaticon2-fax text-muted"></i>
            </a>
            <a href="#" class="btn btn-light btn-hover-primary btn-sm btn-icon mr-2">
                <i class="flaticon2-settings text-muted"></i>
            </a>
            <!--end::Actions-->
        </div>
        <!--end::Toolbar-->
    </div>
</div>
<!--end::Subheader-->