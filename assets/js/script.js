/*
    Purpose: Provides client-side interactivity for the Event Reminder Application.
    This script handles tab navigation, modal dialogs, and form validation
    to create a responsive and dynamic user experience without a framework.
*/

document.addEventListener('DOMContentLoaded', () => {

    // --- Tab Navigation ---
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const targetId = link.dataset.tab;

            // Update active state for links
            tabLinks.forEach(innerLink => innerLink.classList.remove('active'));
            link.classList.add('active');

            // Show/hide content
            tabContents.forEach(content => {
                if (content.id === targetId) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });

    // --- Modal Handling ---
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    const closeModalButtons = document.querySelectorAll('.close-btn, .modal-cancel');
    const modalOverlay = document.querySelector('.modal');

    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(button.dataset.modalTarget);
            openModal(modal);
        });
    });

    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                const modal = document.querySelector('.modal');
                closeModal(modal);
            }
        });
    }


    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            closeModal(modal);
        });
    });

    function openModal(modal) {
        if (modal == null) return;
        modal.style.display = 'block';
    }

    function closeModal(modal) {
        if (modal == null) return;
        modal.style.display = 'none';
    }


    // --- Form Validation (Example for Registration) ---
    const registerForm = document.querySelector('#register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = registerForm.querySelector('#password');
            const confirmPassword = registerForm.querySelector('#confirm_password');
            const name = registerForm.querySelector('#name');
            const email = registerForm.querySelector('#email');

            let isValid = true;
            
            // Simple validation checks
            if (name.value.trim() === '') {
                alert('Name is required.');
                isValid = false;
            }
            if (email.value.trim() === '' || !email.value.includes('@')) {
                 alert('A valid email is required.');
                isValid = false;
            }
            if (password.value.length < 6) {
                alert('Password must be at least 6 characters long.');
                isValid = false;
            }
            if (password.value !== confirmPassword.value) {
                alert('Passwords do not match.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Stop form submission
            }
        });
    }

});
