// Main JavaScript for The Pembina Pint and Restaurant

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (mobileMenu && !mobileMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    });

    initHeroSlider();
    initNewsletterForm();
});

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('input-error');
        } else {
            input.classList.remove('input-error');
        }
    });
    
    return isValid;
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('main') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-CA', {
        style: 'currency',
        currency: 'CAD'
    }).format(amount);
}

// Popup Alert System
function showPopupAlert(type, message) {
    // Remove existing alerts
    const existingAlert = document.getElementById('popup-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert element
    const alert = document.createElement('div');
    alert.id = 'popup-alert';
    alert.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 ${
        type === 'success' 
            ? 'bg-green-500 text-white' 
            : type === 'error' 
            ? 'bg-red-500 text-white' 
            : 'bg-blue-500 text-white'
    }`;
    alert.style.minWidth = '300px';
    alert.style.maxWidth = '400px';
    
    // Icon based on type
    const icon = type === 'success' 
        ? '<i class="fas fa-check-circle mr-2"></i>' 
        : type === 'error' 
        ? '<i class="fas fa-exclamation-circle mr-2"></i>' 
        : '<i class="fas fa-info-circle mr-2"></i>';
    
    alert.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                ${icon}
                <span class="font-semibold">${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(alert);
    
    // Animate in
    setTimeout(() => {
        alert.style.opacity = '1';
        alert.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (alert.parentElement) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 300);
        }
    }, 4000);
}

// AJAX Add to Cart Function
function addToCartAjax(productId, quantity = 1, options = {}, callback = null) {
    const csrfToken = getCSRFToken();
    if (!csrfToken) {
        if (callback) callback(false, 'Security token not found. Please refresh the page.');
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    if (Object.keys(options).length > 0) {
        formData.append('options', JSON.stringify(options));
    }
    
    fetch(`${window.BASE_URL || ''}/cart/add`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (callback) {
            callback(data.success, data.message || (data.success ? 'Item added to cart!' : 'Failed to add item'), data.cart_count || 0);
        } else {
            if (data.success) {
                showPopupAlert('success', data.message || 'Item added to cart!');
                updateCartCount(data.cart_count || 0);
            } else {
                showPopupAlert('error', data.message || 'Failed to add item to cart');
            }
        }
    })
    .catch(error => {
        console.error('Add to cart error:', error);
        const errorMsg = 'An error occurred. Please try again.';
        if (callback) {
            callback(false, errorMsg, 0);
        } else {
            showPopupAlert('error', errorMsg);
        }
    });
}

// Update cart count in header
function updateCartCount(count) {
    const badge = document.getElementById('cart-count-badge');
    const badgeMobile = document.getElementById('cart-count-badge-mobile');
    
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    
    if (badgeMobile) {
        if (count > 0) {
            badgeMobile.textContent = count;
            badgeMobile.classList.remove('hidden');
        } else {
            badgeMobile.classList.add('hidden');
        }
    }
}

// Utility function to get CSRF token
function getCSRFToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

// AJAX helper
function makeRequest(url, method = 'GET', data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .catch(error => {
        console.error('Request failed:', error);
        return { success: false, error: error.message };
    });
}

// Hero slider
function initHeroSlider() {
    const slider = document.querySelector('.hero-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-slider-dot');
    const prevBtn = document.querySelector('.hero-slider-btn.prev');
    const nextBtn = document.querySelector('.hero-slider-btn.next');
    let currentIndex = 0;
    let interval = null;

    const showSlide = (index) => {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
        currentIndex = index;
    };

    const nextSlide = () => {
        const nextIndex = (currentIndex + 1) % slides.length;
        showSlide(nextIndex);
    };

    const prevSlide = () => {
        const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    };

    const startAutoplay = () => {
        if (slides.length <= 1) return;
        stopAutoplay();
        interval = setInterval(nextSlide, 6000);
    };

    const stopAutoplay = () => {
        if (interval) {
            clearInterval(interval);
            interval = null;
        }
    };

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const index = parseInt(dot.dataset.slide, 10);
            showSlide(index);
            startAutoplay();
        });
    });

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            startAutoplay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            startAutoplay();
        });
    }

    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);

    showSlide(0);
    startAutoplay();
}

function initNewsletterForm() {
    const form = document.getElementById('newsletter-form');
    if (!form) return;

    const feedback = document.getElementById('newsletter-feedback');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch(`${window.BASE_URL || ''}/newsletter/subscribe`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            feedback.textContent = data.message || '';
            feedback.className = data.success ? 'text-green-100' : 'text-red-200';
            if (data.success) {
                form.reset();
            }
        })
        .catch(() => {
            feedback.textContent = 'Something went wrong. Please try again.';
            feedback.className = 'text-red-200';
        });
    });
}

