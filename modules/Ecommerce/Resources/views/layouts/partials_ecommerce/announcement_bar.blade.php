@php
    $ecommerceConfig = \App\Models\Tenant\ConfigurationEcommerce::first();
@endphp
@if($ecommerceConfig && $ecommerceConfig->publicidad_activa)
    <style>
        .announcement-bar {
            position: sticky;
            top: 0;
            z-index: 10001;
            color: white;
            padding: 8px 0;
            font-size: 14px;
            font-weight: 500;
            display: none;
            width: 100%;
            background-color: {{ $ecommerceConfig->publicidad_color_fondo }};
            transition: transform 0.3s ease-in-out;
            cursor: pointer;
        }
        .announcement-bar:hover {
            transform: scale(1.01); /* Reduced slightly to 1.01 for better stability in fixed layouts, but user asked for 1.02 */
            transform: scale(1.02);
        }
        .announcement-link {
            color: white !important;
            text-decoration: none;
            display: block;
            transition: transform 0.3s ease-in-out;
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
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease-in-out;
        }
        .announcement-bar:hover .close-announcement {
            /* Counter-scale to keep the button at 1:1 size while the bar scales to 1.02 */
            transform: translateY(-50%) scale(0.98);
        }
        .close-announcement:focus {
            outline: none;
        }
    </style>
    <div id="announcement-bar" class="announcement-bar">
        <div class="container text-center position-relative">
            <a href="{{ $ecommerceConfig->publicidad_link ?? '#' }}" target="_blank" class="announcement-link">
                {{ $ecommerceConfig->publicidad_texto }}
            </a>
            <button type="button" class="close-announcement" aria-label="Close" onclick="closeAnnouncementBar()">
                <span>&times;</span>
            </button>
        </div>
    </div>
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
@endif
