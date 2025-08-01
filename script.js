document.addEventListener('DOMContentLoaded', () => {
    const loginButton = document.getElementById('loginButton');
    const loginEmail = document.getElementById('loginEmail');
    const loginPassword = document.getElementById('loginPassword');

    function validateName(name) {
        const nameRegex = /^[a-zA-Z.-]+$/;
        return nameRegex.test(name);
    }

    function validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@(gmail|yahoo|hotmail)\.com$/;
        return emailRegex.test(email);
    }

    function validatePassword(pass, cpass) {
        const passRegex = /^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/;
        return passRegex.test(pass) && pass === cpass;
    }

    function validateDOB(dob) {
        if (!dob) return false;
        const dobDate = new Date(dob);
        const today = new Date('2025-05-21');
        let age = today.getFullYear() - dobDate.getFullYear();
        const monthDiff = today.getMonth() - dobDate.getMonth();
        const dayDiff = today.getDate() - dobDate.getDate();
        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age--;
        }
        return age >= 18;
    }

    function validateCountry(country) {
        return country !== '';
    }

    document.getElementById('fname').addEventListener('blur', () => {
        const name = document.getElementById('fname').value;
        const warning = document.getElementById('fname-warning');
        if (!name) {
            warning.textContent = '* Name is required';
        } else if (!validateName(name)) {
            warning.textContent = "* Name can only contain letters, '.', and '-'";
        } else {
            warning.textContent = '';
        }
    });

    document.getElementById('mail').addEventListener('blur', () => {
        const email = document.getElementById('mail').value;
        const warning = document.getElementById('mail-warning');
        if (!email) {
            warning.textContent = '* Email is required';
        } else if (!validateEmail(email)) {
            warning.textContent = '* Email must be a valid Gmail, Yahoo, or Hotmail address';
        } else {
            warning.textContent = '';
        }
    });

    document.getElementById('pass').addEventListener('blur', () => {
        const pass = document.getElementById('pass').value;
        const warning = document.getElementById('pass-warning');
        if (!pass) {
            warning.textContent = '* Password is required';
        } else if (!/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/.test(pass)) {
            warning.textContent = '* Password must be 8+ chars, 1 letter, 1 number, 1 symbol';
        } else {
            warning.textContent = '';
        }
    });

    document.getElementById('cpass').addEventListener('blur', () => {
        const pass = document.getElementById('pass').value;
        const cpass = document.getElementById('cpass').value;
        const warning = document.getElementById('cpass-warning');
        if (!cpass) {
            warning.textContent = '* Confirm Password is required';
        } else if (pass !== cpass) {
            warning.textContent = '* Passwords do not match';
        } else {
            warning.textContent = '';
        }
    });

    document.getElementById('birthday').addEventListener('blur', () => {
        const dob = document.getElementById('birthday').value;
        const warning = document.getElementById('birthday-warning');
        if (!dob) {
            warning.textContent = '* Date of birth is required';
        } else if (!validateDOB(dob)) {
            warning.textContent = '* You must be at least 18 years old';
        } else {
            warning.textContent = '';
        }
    });

    document.getElementById('country').addEventListener('blur', () => {
        const country = document.getElementById('country').value;
        const warning = document.getElementById('country-warning');
        if (!validateCountry(country)) {
            warning.textContent = '* Country is required';
        } else {
            warning.textContent = '';
        }
    });

    function togglePassword() {
        const passInput = document.getElementById('pass');
        const cpassInput = document.getElementById('cpass');
        if (passInput && cpassInput) {
            if (passInput.type === 'password') {
                passInput.type = 'text';
                cpassInput.type = 'text';
            } else {
                passInput.type = 'password';
                cpassInput.type = 'password';
            }
        }

        const passwordField = document.getElementById('passwordField');
        const showPassword = document.getElementById('showPassword');
        if (passwordField && showPassword) {
            if (showPassword.checked) {
                passwordField.textContent = showPassword.dataset.password || '********';
            } else {
                passwordField.textContent = '********';
            }
        }
    }
    function confirmRegistration() {
        fetch('process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=confirm_registration'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                alert(data.error);
                window.location.href = 'index.html';
            }
        })
        .catch(error => {
            alert('Error: ' + error);
            window.location.href = 'index.html';
        });
    }

    function goBack() {
        window.location.href = 'index.html';
    }

    function validateForm(event) {
        event.preventDefault();
        const name = document.getElementById('fname').value;
        const email = document.getElementById('mail').value;
        const pass = document.getElementById('pass').value;
        const cpass = document.getElementById('cpass').value;
        const dob = document.getElementById('birthday').value;
        const country = document.getElementById('country').value;
        const terms = document.getElementById('terms').checked;
        const genderSelected = document.querySelector('input[name="gender"]:checked');
        const errors = [];

        if (name && validateName(name)) {
            document.getElementById('fname-warning').textContent = '';
        } else {
            document.getElementById('fname-warning').textContent = name ? "* Name can only contain letters, '.', and '-'" : '* Name is required';
            errors.push('Invalid name');
        }

        if (genderSelected) {
            document.getElementById('gender-warning').textContent = '';
        } else {
            document.getElementById('gender-warning').textContent = '* Please select a gender';
            errors.push('Gender not selected');
        }

        if (email && validateEmail(email)) {
            document.getElementById('mail-warning').textContent = '';
        } else {
            document.getElementById('mail-warning').textContent = email ? '* Email must be a valid Gmail, Yahoo, or Hotmail address' : '* Email is required';
            errors.push('Invalid email');
        }

        if (pass && /^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/.test(pass)) {
            document.getElementById('pass-warning').textContent = '';
        } else {
            document.getElementById('pass-warning').textContent = pass ? '* Password must be 8+ chars, 1 letter, 1 number, 1 symbol' : '* Password is required';
            errors.push('Invalid password');
        }

        if (cpass && pass === cpass) {
            document.getElementById('cpass-warning').textContent = '';
        } else {
            document.getElementById('cpass-warning').textContent = cpass ? '* Passwords do not match' : '* Confirm Password is required';
            errors.push('Password mismatch');
        }

        if (dob && validateDOB(dob)) {
            document.getElementById('birthday-warning').textContent = '';
        } else {
            document.getElementById('birthday-warning').textContent = dob ? '* You must be at least 18 years old' : '* Date of birth is required';
            errors.push('Invalid date of birth');
        }

        if (validateCountry(country)) {
            document.getElementById('country-warning').textContent = '';
        } else {
            document.getElementById('country-warning').textContent = '* Country is required';
            errors.push('Country not selected');
        }

        if (terms) {
            document.getElementById('terms-warning').textContent = '';
        } else {
            document.getElementById('terms-warning').textContent = '* You must accept the terms and conditions';
            errors.push('Terms not accepted');
        }

        if (errors.length > 0) {
            alert('Please fix the following errors:\n- ' + errors.join('\n- '));
        } else {
            const form = document.getElementById('myForm');
            console.log('Form object:', form);
            


            const formData = new FormData(form);
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    return response.text(); 
                } else {
                    throw new Error('Registration failed');
                }
            })
            .then(html => {
                document.documentElement.innerHTML = html; 
                const confirmBtn = document.querySelector('.confirm-btn');
                const gobackBtn = document.querySelector('.goback-btn');
                const showPasswordCheckbox = document.getElementById('showPassword');
                if (confirmBtn) confirmBtn.addEventListener('click', confirmRegistration);
                if (gobackBtn) gobackBtn.addEventListener('click', goBack);
                if (showPasswordCheckbox) {
                    showPasswordCheckbox.dataset.password = formData.get('pass'); 
                    showPasswordCheckbox.addEventListener('change', togglePassword);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    }

    loginButton.addEventListener('click', () => {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('loginEmail', loginEmail.value);
        formData.append('loginPassword', loginPassword.value);

        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('userName', data.name);
                window.location.href = 'dashboard.php';
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    });

    document.getElementById('myForm').addEventListener('submit', validateForm);
    document.getElementById('submitButton').addEventListener('click', validateForm);

    const showPasswordCheckbox = document.getElementById('showPassword');
    if (showPasswordCheckbox) {
        showPasswordCheckbox.addEventListener('change', togglePassword);
    }
});