(function () {
    'use strict';

    function getAppUrl() {
        const meta = document.querySelector('meta[name="app-url"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content').replace(/\/$/, '');
        }

        const path = window.location.pathname || '';
        const base = path.replace(/\/[^/]*$/, '');
        return window.location.origin + (base === '' ? '' : base);
    }

    function getLeadApiUrl() {
        return getAppUrl() + '/api/submit-lead.php';
    }

    function buildRedirectUrl(path) {
        if (!path) {
            return window.location.href.split('#')[0];
        }
        if (/^https?:\/\//i.test(path)) {
            return path;
        }
        const normalized = path.startsWith('/') ? path : '/' + path.replace(/^\.\//, '');
        return getAppUrl() + normalized;
    }

    async function parseLeadResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const raw = await response.text();

        if (contentType.includes('application/json') || raw.trim().startsWith('{')) {
            return JSON.parse(raw);
        }

        throw new Error('Unexpected server response. Please try again.');
    }

    function validateLeadForm(form) {
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        const course = form.querySelector('[name="course"]');
        const message = form.querySelector('[name="message"]');
        const courseValue = course ? course.value.trim() : '';
        const messageValue = message ? message.value.trim() : '';

        if (courseValue === '' && messageValue === '') {
            const errorText = 'Please select a course or enter a message.';
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Almost there',
                    text: errorText,
                    confirmButtonColor: '#1E73BE'
                });
            } else {
                window.alert(errorText);
            }
            return false;
        }

        return true;
    }

    async function submitLeadForm(form) {
        if (form.dataset.submitting === '1') {
            return;
        }

        if (!validateLeadForm(form)) {
            return;
        }

        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton ? submitButton.textContent : '';
        const redirectPath = form.dataset.redirect || '';
        const successMessage = 'Your enquiry has been submitted successfully. Our team will contact you shortly.';

        form.dataset.submitting = '1';
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
        }

        if (window.Swal) {
            Swal.fire({
                title: 'Submitting...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });
        }

        try {
            const response = await fetch(getLeadApiUrl(), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const data = await parseLeadResponse(response);

            if (window.Swal && Swal.isVisible()) {
                Swal.close();
            }

            if (data.success) {
                form.reset();

                if (window.Swal) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Thank You!',
                        text: successMessage,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#1E73BE',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }

                const redirectTarget = redirectPath || data.redirect || '';
                if (redirectTarget) {
                    window.location.href = buildRedirectUrl(redirectTarget);
                }

                return;
            }

            if (window.Swal) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: data.message || 'Please check the form and try again.',
                    confirmButtonColor: '#1E73BE'
                });
            }
        } catch (error) {
            if (window.Swal && Swal.isVisible()) {
                Swal.close();
            }
            if (window.Swal) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: error.message || 'We could not submit your enquiry right now. Please try again.',
                    confirmButtonColor: '#1E73BE'
                });
            }
        } finally {
            form.dataset.submitting = '0';
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    }

    function initLeadForms() {
        document.querySelectorAll('.lw-lead-form').forEach(function (form) {
            if (form.dataset.lwBound === '1') {
                return;
            }

            form.dataset.lwBound = '1';
            form.setAttribute('method', 'post');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopPropagation();
                submitLeadForm(form);
            });

            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    submitLeadForm(form);
                });
            }
        });
    }

    function initNavbar() {
        const navbarCollapseElement = document.getElementById('mainNavbar');
        if (!navbarCollapseElement || typeof bootstrap === 'undefined') {
            return null;
        }

        const navbarCollapse = bootstrap.Collapse.getOrCreateInstance(navbarCollapseElement, { toggle: false });

        document.querySelectorAll('#mainNavbar .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                const navbarToggler = document.querySelector('.navbar-toggler');
                if (navbarCollapseElement.classList.contains('show') && navbarToggler && window.getComputedStyle(navbarToggler).display !== 'none') {
                    navbarCollapse.hide();
                }
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (event) {
                const href = this.getAttribute('href');
                if (!href || href === '#') {
                    return;
                }

                const target = document.querySelector(href);
                if (!target) {
                    return;
                }

                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                const navbarToggler = document.querySelector('.navbar-toggler');
                if (navbarCollapseElement.classList.contains('show') && navbarToggler && window.getComputedStyle(navbarToggler).display !== 'none') {
                    navbarCollapse.hide();
                }
            });
        });

        return navbarCollapse;
    }

    function initCourseFilters() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        const courseCards = document.querySelectorAll('.course-card');

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const category = this.dataset.filter;
                filterButtons.forEach(function (btn) {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                courseCards.forEach(function (card) {
                    card.style.display = category === 'all' || card.dataset.category === category ? 'block' : 'none';
                });
            });
        });
    }

    function initVideoModals() {
        document.querySelectorAll('.modal').forEach(function (modalElement) {
            const iframe = modalElement.querySelector('iframe');
            const video = modalElement.querySelector('video');

            modalElement.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                if (!trigger) {
                    return;
                }

                const src = trigger.getAttribute('data-video-src') || '';
                const type = trigger.getAttribute('data-video-type') || 'url';

                if (type === 'file') {
                    if (video) {
                        video.classList.remove('d-none');
                        video.src = src;
                    }
                    if (iframe) {
                        iframe.classList.add('d-none');
                        iframe.src = '';
                    }
                    return;
                }

                let embedSrc = src;
                if (src.includes('youtube.com/watch?v=')) {
                    embedSrc = src.replace('watch?v=', 'embed/');
                } else if (src.includes('youtu.be/')) {
                    embedSrc = src.replace('youtu.be/', 'youtube.com/embed/');
                }

                if (embedSrc.includes('youtube.com/embed/') && !embedSrc.includes('?')) {
                    embedSrc += '?rel=0';
                }

                if (iframe) {
                    iframe.classList.remove('d-none');
                    iframe.src = embedSrc;
                }
                if (video) {
                    video.classList.add('d-none');
                    video.pause();
                    video.removeAttribute('src');
                }
            });

            modalElement.addEventListener('hidden.bs.modal', function () {
                if (iframe) {
                    iframe.src = '';
                    iframe.classList.add('d-none');
                }
                if (video) {
                    video.pause();
                    video.removeAttribute('src');
                    video.classList.add('d-none');
                }
            });
        });
    }

    function initFlashAlerts() {
        if (!window.Swal) {
            return;
        }

        document.querySelectorAll('.alert-flash').forEach(function (alertNode) {
            const isSuccess = alertNode.classList.contains('alert-success');
            Swal.fire({
                icon: isSuccess ? 'success' : 'error',
                title: isSuccess ? 'Thank you!' : 'Notice',
                text: alertNode.textContent.trim(),
                confirmButtonColor: '#1E73BE'
            });
            alertNode.remove();
        });
    }

    function initPage() {
        initLeadForms();
        initFlashAlerts();

        try {
            initNavbar();
            initCourseFilters();
            initVideoModals();
        } catch (error) {
            console.error('LearnWise UI init error:', error);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPage);
    } else {
        initPage();
    }
})();
