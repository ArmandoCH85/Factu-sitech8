<!DOCTYPE html>
<html lang="es">

<!-- Mirrored from portotheme.com/html/porto_ecommerce/demo-6/ by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 07 Sep 2019 03:39:38 GMT -->

<head>
    @php($pageCompany = $company ?? $vc_company ?? null)
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ data_get($pageCompany, 'title_web') ?: data_get($pageCompany, 'trade_name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="ecommerce, {{ data_get($pageCompany, 'trade_name') }}" />
    <meta name="description" content="{{ $ecommerceDescription ?? 'eCommerce' }}" />
    <meta name="author" content="SW-THEMES">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ data_get($pageCompany, 'title_web') ?: data_get($pageCompany, 'trade_name') }}" />
    <meta property="og:description" content="{{ $ecommerceDescription ?? 'eCommerce' }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    @php($headerLogo = data_get($company ?? null, 'logo') ?: data_get($information ?? null, 'logo'))
    <meta property="og:image" content="{{ $headerLogo ? asset('storage/uploads/logos/'.$headerLogo) : asset('logo/tulogo.png') }}" />
    <meta property="og:site_name" content="{{ data_get($pageCompany, 'title_web') ?: data_get($pageCompany, 'trade_name') }}" />

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ data_get($pageCompany, 'title_web') ?: data_get($pageCompany, 'trade_name') }}" />
    <meta name="twitter:description" content="{{ $ecommerceDescription ?? 'eCommerce' }}" />
    <meta name="twitter:image" content="{{ $headerLogo ? asset('storage/uploads/logos/'.$headerLogo) : asset('logo/tulogo.png') }}" />

    <!-- Schema.org JSON-LD (ItemList para listado de productos) -->
    @if(isset($products) && count($products))
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Listado de productos",
        "itemListElement": [
            @foreach($products as $index => $product)
            {
                "@type": "Product",
                "position": {{ $index + 1 }},
                "name": "{{ addslashes($product->name) }}",
                "image": "{{ $product->image_url ?? ($headerLogo ? asset('storage/uploads/logos/'.$headerLogo) : asset('logo/tulogo.png')) }}",
                "url": "{{ route('ecommerce.product.show', $product->slug) }}"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    @endif

    <!-- Favicon1 -->
    <link rel="icon" type="image/x-icon" href="{{ asset('porto-ecommerce/assets/images/icons/favicon.svg') }}">

    <!-- Plugins CSS File -->
    <link rel="stylesheet" href="{{ asset('porto-ecommerce/assets/css/bootstrap.min.css') }}">

    <!-- Main CSS File -->
    <link rel="stylesheet" href="{{ asset('porto-ecommerce/assets/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('porto-ecommerce/assets/css/custom.css') }}">

    <link rel="stylesheet" href="{{ asset('porto-ecommerce/assets/css/rating.css') }}">

    <!-- Fontawesome -->
    <link rel="stylesheet" href="{{ asset('porto-ecommerce/assets/font-awesome/css/fontawesome-all.min.css') }}">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('porto-light/css/styles_ecommerce.css') }}" />

    <style>
        .announcement-bar {
            position: sticky;
            top: 0;
            z-index: 10001; /* Higher than header */
            color: white;
            padding: 8px 0;
            font-size: 14px;
            font-weight: 500;
            display: none;
            width: 100%;
        }
        .announcement-link {
            color: white !important;
            text-decoration: none;
            display: block;
        }
        .announcement-link:hover {
            text-decoration: underline;
        }
        .close-announcement {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }
        .close-announcement:focus {
            outline: none;
        }
    </style>
</head>

<body data-company-title="{{ data_get($pageCompany, 'title_web') ?: data_get($pageCompany, 'trade_name') }}">

    <?php
        $configurationModel = \App\Models\Tenant\Configuration::first();
        $ecommerceConfiguration = \App\Models\Tenant\ConfigurationEcommerce::first();
        $phoneWhatsapp = $ecommerceConfiguration->phone_whatsapp ?? $configurationModel->phone_whatsapp ?? null;
        $showWhatsapp = ($configurationModel && ($configurationModel->enable_whatsapp ?? false)) && !empty($phoneWhatsapp);
        $waPhone = $phoneWhatsapp ? preg_replace('/\D+/', '', $phoneWhatsapp) : '';
        $waText = rawurlencode('Hola, tengo una consulta desde la tienda online');
        $waLink = $waPhone ? "https://wa.me/{$waPhone}?text={$waText}" : '';
    ?>

<body>
    @php
        $config_publicidad = \App\Models\Tenant\ConfigurationEcommerce::first();
    @endphp

    @if($config_publicidad && $config_publicidad->publicidad_activa)
        <div id="announcement-bar" class="announcement-bar" style="background-color: {{ $config_publicidad->publicidad_color_fondo }};">
            <div class="container text-center position-relative">
                <a href="{{ $config_publicidad->publicidad_link ?? '#' }}" target="_blank" class="announcement-link">
                    {{ $config_publicidad->publicidad_texto }}
                </a>
                <button type="button" class="close-announcement" aria-label="Close" onclick="closeAnnouncementBar()">
                    <span>&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="page-wrapper">

        @include('ecommerce::layouts.partials_ecommerce.header')
        <main class="main">
        @yield('content')
        </main><!-- End .main -->

        <footer class="footer">
            @include('ecommerce::layouts.partials_ecommerce.footer')
        </footer><!-- End .footer -->
    </div><!-- End .page-wrapper -->

    <div class="mobile-menu-overlay"></div><!-- End .mobil-menu-overlay -->

    <div class="mobile-menu-container">

        @include('ecommerce::layouts.partials_ecommerce.mobile_menu')

    </div><!-- End .mobile-menu-container -->

    <div class="newsletter-popup mfp-hide" id="newsletter-popup-form">
        <!-- style="background-image: url(assets/images/newsletter_popup_bg.jpg)" -->
        <div class="newsletter-popup-content">
            <img src="{{ asset('porto-ecommerce/assets/images/logo-black.png') }}" alt="Logo" class="logo-newsletter">
            <h2>BE THE FIRST TO KNOW</h2>
            <p>Subscribe to the Porto eCommerce newsletter to receive timely updates from your favorite products.</p>
            <form action="#">
                <div class="input-group">
                    <input type="email" class="form-control" id="newsletter-email" name="newsletter-email"
                        placeholder="Email address" required>
                    <input type="submit" class="btn" value="Go!">
                </div><!-- End .from-group -->
            </form>
            <div class="newsletter-subscribe">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" value="1">
                        Don't show this popup again
                    </label>
                </div>
            </div>
        </div><!-- End .newsletter-popup-content -->
    </div><!-- End .newsletter-popup -->

    <a id="scroll-top" href="#top" title="Top" role="button"><i class="icon-angle-up"></i></a>

    <!-- Plugins JS File -->
    <script src="{{ asset('porto-ecommerce/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/plugins.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('porto-ecommerce/assets/js/cart.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/main.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/vue.min.js') }}"></script>
    @stack('scripts')
    
    <script>
        function closeAnnouncementBar() {
            const bar = document.getElementById('announcement-bar');
            if (bar) {
                bar.style.display = 'none';
                localStorage.setItem('hide_announcement_bar', 'true');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const bar = document.getElementById('announcement-bar');
            if (bar && !localStorage.getItem('hide_announcement_bar')) {
                bar.style.display = 'block';
            }
        });
    </script>
</body>

<!-- Mirrored from portotheme.com/html/porto_ecommerce/demo-6/ by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 07 Sep 2019 03:39:54 GMT -->

</html>
