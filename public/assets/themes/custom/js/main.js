/**
 * Custom Theme JavaScript
 * Author: Your Name
 * Version: 1.0
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initCustomTheme();
    });

    function initCustomTheme() {
        hidePreloader();
        initNavbar();
        initScrollEffects();
        initAnimations();
        initTyped();
        initSmoothScroll();
        initFormValidation();
        initTooltips();
        initLazyLoading();
    }

    // Hide preloader
    function hidePreloader() {
        setTimeout(function() {
            $('#szn-preloader').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 500);
    }

    // Navbar functionality
    function initNavbar() {
        const navbar = $('.navbar');
        const navbarToggler = $('.navbar-toggler');
        const navbarCollapse = $('.navbar-collapse');

        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(window).scrollTop() > 50) {
                navbar.addClass('navbar-scrolled');
            } else {
                navbar.removeClass('navbar-scrolled');
            }
        });

        // Close mobile menu when clicking on a link
        $('.navbar-nav .nav-link').on('click', function() {
            if (navbarCollapse.hasClass('show')) {
                navbarToggler.click();
            }
        });

        // Active navigation highlighting
        $(window).scroll(function() {
            const scrollPos = $(window).scrollTop() + 100;
            
            $('.navbar-nav .nav-link').each(function() {
                const href = $(this).attr('href');
                if (href.startsWith('#')) {
                    const target = $(href);
                    if (target.length) {
                        const targetTop = target.offset().top;
                        const targetBottom = targetTop + target.outerHeight();
                        
                        if (scrollPos >= targetTop && scrollPos < targetBottom) {
                            $('.navbar-nav .nav-link').removeClass('active');
                            $(this).addClass('active');
                        }
                    }
                }
            });
        });
    }

    // Scroll effects
    function initScrollEffects() {
        // Parallax effect for hero section
        $(window).scroll(function() {
            const scrolled = $(window).scrollTop();
            const parallax = $('.hero-background');
            const speed = scrolled * 0.5;
            
            if (parallax.length) {
                parallax.css('transform', 'translateY(' + speed + 'px)');
            }
        });

        // Reveal animations on scroll
        const revealElements = $('.fade-in, [data-aos]');
        
        function revealOnScroll() {
            revealElements.each(function() {
                const element = $(this);
                const elementTop = element.offset().top;
                const elementBottom = elementTop + element.outerHeight();
                const viewportTop = $(window).scrollTop();
                const viewportBottom = viewportTop + $(window).height();
                
                if (elementBottom > viewportTop && elementTop < viewportBottom) {
                    element.addClass('visible');
                }
            });
        }

        $(window).on('scroll', revealOnScroll);
        revealOnScroll(); // Initial check
    }

    // Animation utilities
    function initAnimations() {
        // Counter animation
        $('.counter').each(function() {
            const $this = $(this);
            const countTo = $this.attr('data-count');
            
            $({ countNum: $this.text() }).animate({
                countNum: countTo
            }, {
                duration: 2000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(this.countNum);
                }
            });
        });

        // Progress bar animation
        $('.progress-bar').each(function() {
            const $this = $(this);
            const width = $this.attr('data-width') || $this.css('width');
            
            $this.css('width', '0%').animate({
                width: width
            }, {
                duration: 1500,
                easing: 'easeOutQuart'
            });
        });

        // Stagger animation for cards
        $('.service-card, .resume-item, .skill-item').each(function(index) {
            $(this).css('animation-delay', (index * 0.1) + 's');
        });
    }

    // Typed.js initialization
    function initTyped() {
        if (typeof Typed !== 'undefined' && $('#typed-strings').length) {
            const typedStrings = $('#typed-strings').data('strings');
            if (typedStrings && typedStrings.length > 0) {
                new Typed('#typed-strings', {
                    strings: typedStrings,
                    typeSpeed: 50,
                    backSpeed: 30,
                    backDelay: 2000,
                    loop: true,
                    showCursor: true,
                    cursorChar: '|'
                });
            }
        }
    }

    // Smooth scrolling
    function initSmoothScroll() {
        $('a[href^="#"]:not(.resume-nav a)').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            
            if (target.length) {
                e.preventDefault();
                const offset = $('.navbar').outerHeight() || 0;
                
                $('html, body').animate({
                    scrollTop: target.offset().top - offset - 20
                }, 1000, 'easeInOutQuart');
            }
        });
    }

    // Form validation and submission
    function initFormValidation() {
        const contactForm = $('#contact-me-form');
        
        if (contactForm.length) {
            // Real-time validation
            contactForm.find('input, textarea').on('blur', function() {
                validateField($(this));
            });

            // Form submission
            contactForm.on('submit', function(e) {
                e.preventDefault();
                
                if (validateForm(contactForm)) {
                    submitForm(contactForm);
                }
            });
        }
    }

    function validateField(field) {
        const value = field.val().trim();
        const fieldName = field.attr('name');
        let isValid = true;
        let errorMessage = '';

        // Remove existing error styling
        field.removeClass('is-invalid');
        field.next('.invalid-feedback').remove();

        // Validation rules
        switch (fieldName) {
            case 'name':
                if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Имя должно содержать минимум 2 символа';
                }
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Введите корректный email адрес';
                }
                break;
            case 'subject':
                if (value.length < 3) {
                    isValid = false;
                    errorMessage = 'Тема должна содержать минимум 3 символа';
                }
                break;
            case 'body':
                if (value.length < 10) {
                    isValid = false;
                    errorMessage = 'Сообщение должно содержать минимум 10 символов';
                }
                break;
        }

        if (!isValid) {
            field.addClass('is-invalid');
            field.after('<div class="invalid-feedback">' + errorMessage + '</div>');
        }

        return isValid;
    }

    function validateForm(form) {
        let isValid = true;
        
        form.find('input[required], textarea[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        return isValid;
    }

    function submitForm(form) {
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Отправка...');

        // Submit via AJAX
        $.ajax({
            url: form.attr('action') || window.location.href,
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 200) {
                    showNotification('Сообщение успешно отправлено!', 'success');
                    form[0].reset();
                } else {
                    showNotification(response.message || 'Произошла ошибка при отправке', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Произошла ошибка при отправке сообщения';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showNotification(errorMessage, 'error');
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    }

    // Tooltip initialization
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            const $this = $(this);
            const tooltipText = $this.data('tooltip');
            
            $this.on('mouseenter', function() {
                const tooltip = $('<div class="tooltip-custom-content">' + tooltipText + '</div>');
                $('body').append(tooltip);
                
                const offset = $this.offset();
                tooltip.css({
                    position: 'absolute',
                    top: offset.top - tooltip.outerHeight() - 10,
                    left: offset.left + ($this.outerWidth() / 2) - (tooltip.outerWidth() / 2),
                    zIndex: 9999
                });
                
                tooltip.fadeIn(200);
            });
            
            $this.on('mouseleave', function() {
                $('.tooltip-custom-content').fadeOut(200, function() {
                    $(this).remove();
                });
            });
        });
    }

    // Lazy loading for images
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Utility functions
    function showNotification(message, type = 'info', duration = 5000) {
        const notification = $(`
            <div class="notification notification-${type}">
                <div class="notification-content">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close">&times;</button>
            </div>
        `);

        $('body').append(notification);
        
        notification.fadeIn(300);
        
        if (duration > 0) {
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, duration);
        }

        notification.find('.notification-close').on('click', function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    function getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Easing functions
    $.easing.easeInOutQuart = function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
        return -c/2 * ((t-=2)*t*t*t - 2) + b;
    };

    $.easing.easeOutQuart = function (x, t, b, c, d) {
        return -c * ((t=t/d-1)*t*t*t - 1) + b;
    };

    // Expose functions globally if needed
    window.CustomTheme = {
        showNotification: showNotification,
        initCustomTheme: initCustomTheme
    };

})(jQuery);

// Additional CSS for notifications
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 1rem;
        z-index: 9999;
        display: none;
        min-width: 300px;
        border-left: 4px solid;
    }
    
    .notification-success {
        border-left-color: #28a745;
    }
    
    .notification-error {
        border-left-color: #dc3545;
    }
    
    .notification-warning {
        border-left-color: #ffc107;
    }
    
    .notification-info {
        border-left-color: #17a2b8;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        margin-right: 1rem;
    }
    
    .notification-content i {
        margin-right: 0.5rem;
        font-size: 1.2rem;
    }
    
    .notification-success .notification-content i {
        color: #28a745;
    }
    
    .notification-error .notification-content i {
        color: #dc3545;
    }
    
    .notification-warning .notification-content i {
        color: #ffc107;
    }
    
    .notification-info .notification-content i {
        color: #17a2b8;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        line-height: 1;
    }
    
    .notification-close:hover {
        color: #495057;
    }
    
    .navbar-scrolled {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    }
`;

// Inject notification styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);
