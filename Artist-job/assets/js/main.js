$(document).ready(function() {
    // Handle mobile touch delay
    if ('ontouchstart' in window) {
        // Add touch class to body for special touch styling
        $('body').addClass('touch-device');
        
        // Add touchstart event handlers to buttons for faster response
        $('.btn, .category-card').on('touchstart', function() {
            $(this).addClass('btn-touch-active');
        }).on('touchend touchcancel', function() {
            $(this).removeClass('btn-touch-active');
        });
    }
    
    // Add click effect to category cards with improved feel
    $('.category-card').on('click', function() {
        const card = $(this);
        card.addClass('category-clicked');
        setTimeout(() => {
            card.removeClass('category-clicked');
        }, 300);
    });
    
    // Handle category card click with improved UX
    $('.category-card').click(function() {
        const categoryId = $(this).data('category-id');
        const categoryName = $(this).data('category-name');
        
        // Add a more engaging click effect
        $(this).addClass('category-clicked');
        setTimeout(() => {
            $(this).removeClass('category-clicked');
        }, 300);
        
        // Update modal title with more engaging title
        $('#appModal .modal-title').text(`${categoryName} Applications`);
        
        // Show loading spinner with improved message
        $('#appList').html('<div class="col-12 text-center py-5"><div class="loading-spinner"></div><p class="mt-3 text-muted">Loading amazing applications...</p></div>');
        
        // Show the modal
        $('#appModal').modal('show');
        
        // Fetch apps for the selected category
        $.ajax({
            url: 'get_apps.php',
            type: 'POST',
            data: { category_id: categoryId },
            dataType: 'json',
            success: function(apps) {
                let html = '';
                
                if (apps.error) {
                    html = `<div class="col-12 text-center py-5">
                        <div class="error-icon mb-3"><i class="fas fa-exclamation-circle"></i></div>
                        <h5 class="text-danger">Error Loading Applications</h5>
                        <p class="text-muted mb-3">${apps.error}</p>
                        <button class="btn btn-outline-danger" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Try Again
                        </button>
                    </div>`;
                } else if (apps.length === 0) {
                    html = `<div class="col-12 text-center py-5">
                        <div class="empty-icon mb-3"><i class="fas fa-search"></i></div>
                        <h5 class="text-muted">No Applications Found</h5>
                        <p class="text-muted mb-3">There are currently no applications available for this category.</p>
                        <p class="small text-muted">Please check back later or contact support for assistance.</p>
                    </div>`;
                } else {
                    apps.forEach((app, index) => {
                        // Always use the Instagram app image
                        const imageUrl = "https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8aW5zdGFncmFtJTIwYXBwfGVufDB8fDB8fHww";
                        
                        html += `
                            <div class="col-md-4 mb-4">
                                <div class="app-card" data-form-url="${app.form_url}" style="animation-delay: ${index * 0.1}s;">
                                    <div class="app-img-container">
                                        <img src="${imageUrl}" alt="${app.name}" class="app-img">
                                    </div>
                                    <h5 class="app-title">${app.name}</h5>
                                    <p class="app-desc">${app.description || 'Apply for this opportunity'}</p>
                                    <button class="btn btn-primary btn-sm mt-2 apply-btn">
                                        <i class="fas fa-paper-plane me-1"></i> Apply Now
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                $('#appList').html(html);
                
                // Animate app cards in with a staggered effect
                if (apps.length > 0) {
                    $('.app-card').each(function(index) {
                        const $this = $(this);
                        setTimeout(function() {
                            $this.addClass('fade-in');
                        }, index * 100);
                    });
                }
                
                // Add click handler for app cards
                $('.app-card').click(function() {
                    const formUrl = $(this).data('form-url');
                    if (formUrl && formUrl !== 'https://forms.google.com/...') {
                        window.open(formUrl, '_blank');
                    } else {
                        alert('Form URL is not available yet. Please check back later.');
                    }
                });
            },
            error: function(xhr, status, error) {
                $('#appList').html(`
                    <div class="col-12 text-center py-5">
                        <div class="error-icon mb-3"><i class="fas fa-times-circle"></i></div>
                        <h5 class="text-danger">Connection Error</h5>
                        <p class="text-muted mb-3">Unable to connect to the server. Please check your internet connection.</p>
                        <button class="btn btn-outline-danger" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Try Again
                        </button>
                    </div>
                `);
            }
        });
    });
    
    // Enhanced animation for elements on scroll with better viewport calculation
    function animateOnScroll() {
        // Animate category cards
        $('.category-card').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height() * 0.85; // 85% of viewport height
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('fade-in');
            }
        });
        
        // Animate contact card with a more dramatic effect
        $('.contact-card').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height() * 0.8; // 80% of viewport height
            
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                if (!$(this).hasClass('animated')) {
                    $(this).addClass('animated');
                    
                    // Add sequential animation to child elements
                    const $icon = $(this).find('.contact-card-icon');
                    const $title = $(this).find('h3');
                    const $text = $(this).find('.contact-text');
                    const $email = $(this).find('.contact-email');
                    const $btn = $(this).find('.contact-btn');
                    
                    // Add CSS animations with delays
                    $icon.css({
                        'animation': 'fadeInDown 0.8s cubic-bezier(0.19, 1, 0.22, 1) forwards',
                        'opacity': '0',
                        'transform': 'translateY(-20px)'
                    });
                    
                    $title.css({
                        'animation': 'fadeInUp 0.8s cubic-bezier(0.19, 1, 0.22, 1) 0.2s forwards',
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    });
                    
                    $text.css({
                        'animation': 'fadeInUp 0.8s cubic-bezier(0.19, 1, 0.22, 1) 0.4s forwards',
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    });
                    
                    $email.css({
                        'animation': 'fadeInUp 0.8s cubic-bezier(0.19, 1, 0.22, 1) 0.6s forwards',
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    });
                    
                    $btn.css({
                        'animation': 'fadeInUp 0.8s cubic-bezier(0.19, 1, 0.22, 1) 0.8s forwards',
                        'opacity': '0',
                        'transform': 'translateY(20px)'
                    });
                }
            }
        });
    }
    
    // Run on scroll and resize with performance optimization
    $(window).on('scroll resize', animateOnScroll);
    
    // Initial check after page loads
    setTimeout(animateOnScroll, 300);
    
    // Smooth scroll for anchor links with custom easing
    $('a[href^="#"]').click(function(e) {
        e.preventDefault();
        const target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 1000, function(x, t, b, c, d) {
                // Custom easing function for smoother scrolling
                if ((t/=d/2) < 1) return c/2*t*t*t + b;
                return c/2*((t-=2)*t*t + 2) + b;
            });
        }
    });
    
    // Add hover effect for contact email
    $('.contact-email').hover(
        function() {
            // Add a subtle bounce effect on hover
            $(this).css('transform', 'translateY(-3px)');
        },
        function() {
            // Reset on mouse out
            $(this).css('transform', 'translateY(0)');
        }
    );
    
    // Add spotlight effect that follows cursor
    $('.contact-card').on('mousemove', function(e) {
        const card = $(this);
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        // Update CSS variable for the spotlight position
        card.css('--x', x + '%');
        card.css('--y', y + '%');
    });
    
    // Add modal animation enhancements
    $(document).on('show.bs.modal', '#appModal', function() {
        // Reset content position for animation
        $('.modal-content').css({
            transform: 'translateY(20px)',
            opacity: 0
        });
        
        // Trigger animation after modal is shown
        setTimeout(function() {
            $('.modal-content').css({
                transform: 'translateY(0)',
                opacity: 1
            });
        }, 100);
        
        // Add modal header shine effect
        const $header = $('.modal-header');
        $header.append('<div class="modal-shine"></div>');
        
        setTimeout(function() {
            $('.modal-shine').css('left', '100%');
        }, 500);
    });
    
    // Enhance navbar behavior on scroll
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar');
    const navbarHeight = navbar.offsetHeight;

    window.addEventListener('scroll', function() {
        let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        
        // Scroll down - hide navbar
        if (currentScroll > lastScrollTop && currentScroll > navbarHeight) {
            navbar.style.transform = 'translateY(-100%)';
        } 
        // Scroll up - show navbar
        else if (currentScroll < lastScrollTop) {
            navbar.style.transform = 'translateY(0)';
        }
        
        // Add shadow and background when scrolled
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }, false);
}); 