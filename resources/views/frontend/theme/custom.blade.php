<!--
* My theme for Portfolio
* Author: Lermal
* Version: 1.0
* Description: Собственная тема для портфолио
-->

@php
    $accentColor = $portfolioConfig['accentColor'];
    $accentColorRGB = Utils::getRgbValue($accentColor);
@endphp

<!DOCTYPE html>
<html lang="ru">
<head>
    @include('common.googleAnalytics')
    @if (!empty($portfolioConfig['script']['header']) && $portfolioConfig['script']['header'] != '')
        <script>
            {!!$portfolioConfig['script']['header']!!}
        </script>
    @endif
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta property="og:title" content="{{$portfolioConfig['seo']['title']}}"/>
    <meta property="title" content="{{$portfolioConfig['seo']['title']}}"/>
    <meta name="description" content="{{$portfolioConfig['seo']['description']}}" />
    <meta property="og:description" content="{{$portfolioConfig['seo']['description']}}"/>
    <meta name="author" content="{{$portfolioConfig['seo']['author']}}" />
    <meta property="og:image" content="{{asset($portfolioConfig['seo']['image'])}}" />
    <meta property="og:image:secure_url" content="{{asset($portfolioConfig['seo']['image'])}}" />
    <title>{{$about->name}}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ Utils::getFavicon() }}">

    <!-- Critical CSS - Bootstrap CSS -->
    <link href="{{ asset('assets/common/lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Preload non-critical CSS -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    
    <!-- Icons - Load asynchronously -->
    <link rel="preload" href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet"></noscript>
    
    <link rel="preload" href="{{ asset('assets/common/lib/boxicons/css/boxicons.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/common/lib/boxicons/css/boxicons.min.css') }}" rel="stylesheet"></noscript>
    
    <!-- Libraries - Load asynchronously -->
    <link rel="preload" href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet"></noscript>
    
    <link rel="preload" href="{{ asset('assets/common/lib/aos/aos.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/common/lib/aos/aos.css') }}" rel="stylesheet"></noscript>
    
    <!-- Theme CSS - Load asynchronously -->
    <link rel="preload" href="{{ asset('assets/themes/custom/css/styles.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/themes/custom/css/styles.css') }}" rel="stylesheet"></noscript>
    
    <link rel="preload" href="{{ asset('assets/themes/custom/css/custom.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="{{ asset('assets/themes/custom/css/custom.css') }}" rel="stylesheet"></noscript>
    
    <style>
        :root {
            --accent-color: {{$accentColor}};
            --accent-color-rgb: {{$accentColorRGB}};
        }
        
        .bg-primary {
            background-color: {{$accentColor.' !important'}};
        }
        
        .text-primary {
            color: {{$accentColor.' !important'}};
        }
        
        .border-primary {
            border-color: {{$accentColor.' !important'}};
        }
        
        a {
            color: {{$accentColor}};
        }
        
        a:hover {
            color: rgba({{$accentColorRGB}}, .8);
        }
        
        .btn-primary {
            background-color: {{$accentColor}};
            border-color: {{$accentColor}};
        }
        
        .btn-primary:hover {
            background-color: rgba({{$accentColorRGB}}, .8);
            border-color: rgba({{$accentColorRGB}}, .8);
        }
        
        .form-control:focus {
            border-color: {{$accentColor}};
            box-shadow: 0 0 0 0.2rem rgba({{$accentColorRGB}}, .25);
        }
    </style>
</head>

<body>
    @include('common.preloader2')
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">{{__('custom.nav.home')}}</a>
                    </li>
                    @if ($portfolioConfig['visibility']['about'])
                        <li class="nav-item">
                            <a class="nav-link" href="#about">{{__('custom.nav.about')}}</a>
                        </li>
                    @endif
                    @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] || $portfolioConfig['visibility']['skills'])
                        <li class="nav-item">
                            <a class="nav-link" href="#resume">{{__('custom.nav.resume')}}</a>
                        </li>
                    @endif
                    @if ($portfolioConfig['visibility']['services'])
                        <li class="nav-item">
                            <a class="nav-link" href="#services">{{__('custom.nav.services')}}</a>
                        </li>
                    @endif
                    @if ($portfolioConfig['visibility']['projects'])
                        <li class="nav-item">
                            <a class="nav-link" href="#projects">{{__('custom.nav.projects')}}</a>
                        </li>
                    @endif
                    @if ($portfolioConfig['visibility']['contact'])
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">{{__('custom.nav.contact')}}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-background" style="background-image: url('{{asset($about->cover)}}');"></div>
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row min-vh-100 align-items-center">
                <div class="col-lg-8 mx-auto text-center text-white">
                    <div class="hero-content" data-aos="fade-up">
                        <h1 class="display-4 fw-bold mb-4" style="color: var(--accent-color);">{{$about->name}}</h1>
                        <p class="lead mb-4">
                            <span id="typed-strings"></span>
                        </p>
                        @if ($portfolioConfig['visibility']['cv'])
                            <a href="{{$about->cv}}" class="btn btn-primary btn-lg" download>
                                <i class="fas fa-download me-2"></i>{{__('custom.info.download_cv')}}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <a href="#about" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    @if ($portfolioConfig['visibility']['about'])
    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="about-image" data-aos="fade-right">
                        {!! \App\Helpers\ImageHelper::optimizedImage($about->avatar, $about->name, 'img-fluid rounded-3 shadow') !!}
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content" data-aos="fade-left">
                        <h2 class="section-title mb-4">{{__('custom.sections.about_me')}}</h2>
                        <p class="lead mb-4">{{ $about->description }}</p>
                        
                        <div class="about-info">
                            <div class="info-item">
                                <i class="fas fa-user text-primary me-3"></i>
                                <span class="fw-bold">{{__('custom.info.name')}}:&#160;</span>
                                <span>{{ $about->name }}</span>
                            </div>
                            @if ($about->email && $about->email !== '')
                            <div class="info-item">
                                <i class="fas fa-envelope text-primary me-3"></i>
                                <span class="fw-bold">{{__('custom.info.email')}}:&#160;</span>
                                <span><a href="mailto:{{$about->email}}">{{$about->email}}</a></span>
                            </div>
                            @endif
                            @if ($about->phone && $about->phone !== '')
                            <div class="info-item">
                                <i class="fas fa-phone text-primary me-3"></i>
                                <span class="fw-bold">{{__('custom.info.phone')}}:&#160;</span>
                                <span><a href="tel:{{$about->phone}}">{{$about->phone}}</a></span>
                            </div>
                            @endif
                            @if ($about->address && $about->address !== '')
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                <span class="fw-bold">{{__('custom.info.address')}}:&#160;</span>
                                <span>{{$about->address}}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Links -->
    @if ($about->social_links)
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="social-links" data-aos="zoom-in">
                        @foreach (json_decode($about->social_links) as $social)
                            <a href="{{$social->link}}" target="_blank" class="social-link">
                                <i class="{{$social->iconClass}}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    @endif

    @if ($portfolioConfig['visibility']['experiences'] || $portfolioConfig['visibility']['education'] || $portfolioConfig['visibility']['skills'])
    <!-- Resume Section -->
    <section id="resume" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">{{__('custom.sections.resume')}}</h2>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="resume-nav" data-aos="fade-right">
                        <ul class="nav nav-pills flex-column">
                            @if ($portfolioConfig['visibility']['education'])
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="pill" href="#education-tab">
                                    <i class="fas fa-graduation-cap me-2"></i> {{__('custom.sections.education')}}
                                </a>
                            </li>
                            @endif
                            @if ($portfolioConfig['visibility']['experiences'])
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="pill" href="#experience-tab">
                                    <i class="fas fa-briefcase me-2"></i> {{__('custom.sections.experience')}}
                                </a>
                            </li>
                            @endif
                            @if ($portfolioConfig['visibility']['skills'])
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="pill" href="#skills-tab">
                                    <i class="fas fa-code me-2"></i> {{__('custom.sections.skills')}}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="tab-content" data-aos="fade-left">
                        @if ($portfolioConfig['visibility']['education'])
                        <div class="tab-pane fade show active" id="education-tab">
                            <h3 class="mb-4">{{__('custom.sections.education')}}</h3>
                            @if ($education)
                                @foreach ($education as $value)
                                    <div class="resume-item mb-4">
                                        <div class="resume-item-header">
                                            <h4>{{$value->degree}}</h4>
                                            <span class="badge">{{$value->period}}</span>
                                        </div>
                                        <h5 class="text-muted">{{$value->institution}}</h5>
                                        @if ($value->cgpa && $value->cgpa !== '')
                                            <p class="mb-1"><strong>{{__('custom.education.cgpa')}}:</strong> {{$value->cgpa}}</p>
                                        @endif
                                        @if ($value->thesis && $value->thesis !== '')
                                            <p class="mb-0"><strong>{{__('custom.education.thesis')}}:</strong> {{$value->thesis}}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @endif
                        
                        @if ($portfolioConfig['visibility']['experiences'])
                        <div class="tab-pane fade" id="experience-tab">
                            <h3 class="mb-4">{{__('custom.sections.experience')}}</h3>
                            @if ($experiences)
                                @foreach ($experiences as $experience)
                                    <div class="resume-item mb-4">
                                        <div class="resume-item-header">
                                            <h4>{{$experience->position}}</h4>
                                            <span class="badge">{{$experience->period}}</span>
                                        </div>
                                        <h5 class="text-muted">{{$experience->company}}</h5>
                                        <p>{{$experience->details}}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @endif
                        
                        @if ($portfolioConfig['visibility']['skills'])
                        <div class="tab-pane fade" id="skills-tab">
                            <h3 class="mb-4">{{__('custom.sections.skills')}}</h3>
                            @if (!empty($skills))
                                <div class="row">
                                    @foreach ($skills as $skill)
                                        <div class="col-md-6 mb-3">
                                            <div class="skill-item">
                                                <div class="skill-header">
                                                    <span class="skill-name">{{$skill->name}}</span>
                                                    @if ((int)$portfolioConfig['visibility']['skillProficiency'])
                                                        <span class="skill-percentage">{{$skill->proficiency}}%</span>
                                                    @endif
                                                </div>
                                                @if ((int)$portfolioConfig['visibility']['skillProficiency'])
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar" style="width: {{$skill->proficiency}}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['services'])
    <!-- Services Section -->
    <section id="services" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">{{__('custom.sections.services')}}</h2>
                </div>
            </div>
            <div class="row">
                @if (!empty($services))
                    @foreach ($services as $service)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="service-card" data-aos="zoom-in">
                                <div class="service-icon">
                                    <i class="{{$service->icon}}"></i>
                                </div>
                                <h4>{{$service->title}}</h4>
                                <p>{{$service->details}}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['projects'])
    <!-- Projects Section -->
    <section id="projects" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">{{__('custom.sections.projects')}}</h2>
                </div>
            </div>
            <div 
                id="react-project-root" 
                data-accentcolor="{{$accentColor}}" 
                data-demomode="{{$demoMode}}"
                data-translations="{{ json_encode([
                    'all' => __('custom.projects.all'),
                    'see_details' => __('custom.projects.see_details'),
                    'images' => __('custom.projects.images'),
                    'category' => __('custom.projects.category'),
                    'link' => __('custom.projects.link'),
                    'close' => __('custom.projects.close'),
                    'preview' => __('custom.projects.preview'),
                    'description' => __('custom.projects.description'),
                ]) }}"
            />
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['contact'])
    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">{{__('custom.sections.contact_me')}}</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <form id="contact-me-form" class="contact-form" data-aos="fade-up">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{__('custom.contact.your_name')}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="{{__('custom.contact.your_email')}}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="{{__('custom.contact.subject')}}" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="body" name="body" rows="5" placeholder="{{__('custom.contact.body')}}" required></textarea>
                        </div>
                        @if(env('TURNSTILE_SITE_KEY'))
                        <div class="mb-3 text-center">
                            <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}" data-theme="light"></div>
                        </div>
                        @endif
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> {{__('custom.contact.send_message')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if ($portfolioConfig['visibility']['footer'])
    <!-- Footer -->
    <footer class="footer py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5 class="mb-3" style="color: var(--accent-color);">{{$about->name}}</h5>
                    <p class="mb-0">©{{ now()->year }} {{__('custom.footer.all_rights_reserved')}}</p>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Scripts -->
    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-migrate/jquery-migrate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/typed/typed.js') }}"></script>
    <script src="{{ asset('assets/common/lib/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery.lazy/jquery.lazy.min.js') }}"></script>
    <script src="{{ asset('assets/themes/custom/js/main.js') }}"></script>
    <script src="{{ asset('js/client/frontend/roots/projects.js') }}"></script>
    @if(env('TURNSTILE_SITE_KEY'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    
    <script>
        $(document).ready(function() {
            // Hide preloader
            setTimeout(function() {
                if ($('#szn-preloader').length) {
                    $('#szn-preloader').fadeOut('slow', function() {
                        $(this).remove();
                    });
                }
            }, 500);

            // Initialize lazy loading
            if (typeof $.fn.lazy !== 'undefined') {
                $('.lazy').lazy();
            }

            // Initialize AOS
            AOS.init({
                duration: 1000,
                once: true
            });

            // Initialize resume tabs
            $('.resume-nav .nav-link').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all nav links
                $('.resume-nav .nav-link').removeClass('active');
                
                // Add active class to clicked link
                $(this).addClass('active');
                
                // Hide all tab panes
                $('.tab-content .tab-pane').removeClass('show active');
                
                // Show target tab pane
                var targetTab = $(this).attr('href');
                $(targetTab).addClass('show active');
                
                return false;
            });

            // Typed.js for hero section
            if ($('#typed-strings').length) {
                @if($about->taglines)
                    var typed = new Typed('#typed-strings', {
                        strings: {!! json_encode(json_decode($about->taglines)) !!},
                        typeSpeed: 50,
                        backSpeed: 30,
                        backDelay: 2000,
                        loop: true
                    });
                @endif
            }

            // Smooth scrolling is handled by main.js

            // Обработка отправки формы без validate
            $('#contact-me-form').on('submit', function(e) {
                e.preventDefault();

                const button = $('#contact-me-form button[type="submit"]');
                const originalText = button.html();
                
                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin me-2"></i> {{__('custom.contact.sending')}}');

                $.ajax({
                    url: '{!! route('contact-me') !!}',
                    dataType: 'json',
                    data: $('#contact-me-form').serialize(),
                    type: 'post',
                    success: function(response) {
                        if (response.status === 200) {
                            $('#contact-me-form').trigger('reset');
                        }
                    },
                    error: function(jqXHR, exception) {
                        button.prop('disabled', false);
                        button.html(originalText);
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.html(originalText);
                    }
                });
            });
        });

        // Fallback to hide preloader if jQuery fails
        window.addEventListener('load', function() {
            setTimeout(function() {
                var preloader = document.getElementById('szn-preloader');
                if (preloader) {
                    preloader.style.display = 'none';
                }
            }, 1000);
        });
    </script>
    
    @if (!empty($portfolioConfig['script']['footer']) && $portfolioConfig['script']['footer'] != '')
        <script>
            {!!$portfolioConfig['script']['footer']!!}
        </script>
    @endif
    @include('common.pixelTracking')
</body>
</html>
