/**
 * Main Frontend Script for Online Healthcare Management System
 * Handles UI interactions, live search, form validation, and responsive portal sidebar.
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Mobile Sidebar Toggle for Dashboards
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const portalSidebar = document.querySelector('.portal-sidebar');

    if (sidebarToggleBtn && portalSidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            portalSidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992) {
                if (!portalSidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
                    portalSidebar.classList.remove('show');
                }
            }
        });
    }

    // 2. Doctor Live Search & Specialty Filter
    const doctorSearchInput = document.getElementById('doctorSearchInput');
    const specialtyFilter = document.getElementById('specialtyFilter');
    const doctorCards = document.querySelectorAll('.doctor-item-card');

    function filterDoctors() {
        const query = doctorSearchInput ? doctorSearchInput.value.toLowerCase().trim() : '';
        const specialty = specialtyFilter ? specialtyFilter.value.toLowerCase().trim() : '';

        doctorCards.forEach(card => {
            const name = card.getAttribute('data-name')?.toLowerCase() || '';
            const spec = card.getAttribute('data-specialty')?.toLowerCase() || '';

            const matchesQuery = name.includes(query) || spec.includes(query);
            const matchesSpec = !specialty || spec === specialty || specialty === 'all';

            if (matchesQuery && matchesSpec) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (doctorSearchInput) {
        doctorSearchInput.addEventListener('input', filterDoctors);
    }
    if (specialtyFilter) {
        specialtyFilter.addEventListener('change', filterDoctors);
    }

    // 3. Appointment Date Minimum Attribute (Disallow dates in the past)
    const appointmentDateInput = document.getElementById('appointment_date');
    if (appointmentDateInput) {
        const today = new Date().toISOString().split('T')[0];
        appointmentDateInput.setAttribute('min', today);
    }

    // 4. Password Toggle
    const togglePasswordButtons = document.querySelectorAll('.toggle-password-btn');
    togglePasswordButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const isPassword = targetInput.type === 'password';
                targetInput.type = isPassword ? 'text' : 'password';
                btn.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            }
        });
    });

    // 5. Contact Form Simulation (Homepage)
    const contactForm = document.getElementById('publicContactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const alertBox = document.getElementById('contactSuccessAlert');
            if (alertBox) {
                alertBox.classList.remove('d-none');
                contactForm.reset();
                setTimeout(() => {
                    alertBox.classList.add('d-none');
                }, 6000);
            }
        });
    }

    // 6. Initialize Bootstrap Tooltips & Popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
});
