<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — KIPS College Chakwal ERP</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }

        body {
            margin: 0; padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1f3c 0%, #1E3A5F 40%, #2C3E50 100%);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }

        /* Animated background circles */
        body::before, body::after {
            content: '';
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.03);
        }
        body::before { width: 600px; height: 600px; top: -150px; left: -150px; }
        body::after  { width: 400px; height: 400px; bottom: -100px; right: -100px; }

        .login-wrapper {
            width: 100%; max-width: 440px;
            padding: 16px; z-index: 10;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Card Header */
        .card-header-custom {
            background: linear-gradient(135deg, #1E3A5F, #2C3E50);
            padding: 36px 32px 28px;
            text-align: center;
        }

        .logo-circle {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.15);
            border: 3px solid rgba(255,255,255,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px; color: #fff;
        }

        .college-name {
            color: #fff; font-size: 18px; font-weight: 700;
            letter-spacing: 0.5px; margin: 0;
        }

        .college-sub {
            color: rgba(255,255,255,0.65); font-size: 12px;
            margin: 4px 0 0; letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Card Body */
        .card-body-custom { padding: 36px 32px 32px; }

        .section-title {
            font-size: 15px; font-weight: 600;
            color: #1E3A5F; margin-bottom: 24px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title::after {
            content: ''; flex: 1; height: 1px;
            background: #e9ecef;
        }

        /* Input Groups */
        .input-group-custom {
            position: relative; margin-bottom: 18px;
        }

        .input-group-custom .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #adb5bd; font-size: 15px; z-index: 5;
            transition: color 0.2s;
        }

        .input-group-custom input {
            width: 100%; padding: 13px 44px 13px 42px;
            border: 2px solid #e9ecef; border-radius: 10px;
            font-size: 14px; color: #2C3E50;
            transition: all 0.25s ease;
            background: #fafafa; outline: none;
        }

        .input-group-custom input:focus {
            border-color: #1E3A5F;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(30,58,95,0.08);
        }

        .input-group-custom input:focus + .input-icon,
        .input-group-custom input:focus ~ .input-icon {
            color: #1E3A5F;
        }

        .input-group-custom input.is-invalid {
            border-color: #E74C3C;
            background: #fff8f8;
        }

        .toggle-password {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #adb5bd; cursor: pointer; font-size: 14px;
            transition: color 0.2s; z-index: 5; padding: 0;
        }
        .toggle-password:hover { color: #1E3A5F; }

        .field-error {
            font-size: 12px; color: #E74C3C;
            margin-top: 5px; display: none;
        }
        .field-error.show { display: block; }

        /* Remember + Forgot */
        .form-options {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 24px;
        }

        .form-check-label { font-size: 13px; color: #6C757D; cursor: pointer; }
        .form-check-input:checked { background-color: #1E3A5F; border-color: #1E3A5F; }

        .forgot-link {
            font-size: 13px; color: #1E3A5F;
            text-decoration: none; font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Login Button */
        .btn-login {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #1E3A5F, #2C3E50);
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center;
            justify-content: center; gap: 8px;
            letter-spacing: 0.3px;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30,58,95,0.4);
        }
        .btn-login:disabled {
            opacity: 0.7; cursor: not-allowed;
        }

        /* Lockout bar */
        .lockout-alert {
            background: #fff3cd; border: 1px solid #ffc107;
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; color: #856404;
            display: none; align-items: center; gap: 8px;
            margin-bottom: 16px;
        }
        .lockout-alert.show { display: flex; }

        /* Attempt counter */
        .attempts-bar {
            display: none; margin-bottom: 12px;
        }
        .attempts-bar.show { display: block; }
        .attempts-dots {
            display: flex; gap: 6px; justify-content: center;
        }
        .attempt-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: #e9ecef; transition: background 0.3s;
        }
        .attempt-dot.used { background: #E74C3C; }

        /* Footer */
        .login-footer {
            text-align: center; padding: 16px 32px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            font-size: 12px; color: #adb5bd;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card-body-custom { padding: 24px 20px 20px; }
            .card-header-custom { padding: 28px 20px 22px; }
            .login-footer { padding: 12px 20px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        {{-- ── HEADER ─────────────────────────────────── --}}
        <div class="card-header-custom">
            <div class="logo-circle">
                {{-- Replace with: <img src="{{ asset('images/logo.png') }}" style="width:50px"> --}}
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <h1 class="college-name">KIPS COLLEGE CHAKWAL</h1>
            <p class="college-sub">College Management ERP System</p>
        </div>

        {{-- ── BODY ──────────────────────────────────── --}}
        <div class="card-body-custom">

            <div class="section-title">
                <i class="fa-solid fa-right-to-bracket fa-sm"></i>
                Sign In to Your Account
            </div>

            {{-- Lockout Alert --}}
            <div class="lockout-alert" id="lockoutAlert">
                <i class="fa-solid fa-lock"></i>
                <span id="lockoutMsg">Account temporarily locked.</span>
            </div>

            {{-- Attempt dots --}}
            <div class="attempts-bar" id="attemptsBar">
                <div class="attempts-dots" id="attemptsDots">
                    @for($i = 0; $i < 5; $i++)
                        <div class="attempt-dot" id="dot{{ $i }}"></div>
                    @endfor
                </div>
                <p class="text-center text-muted mt-1 mb-0" style="font-size:11px" id="attemptsText"></p>
            </div>

            <form id="loginForm" novalidate>
                @csrf

                {{-- Email --}}
                <div class="input-group-custom">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email"
                           placeholder="Email Address"
                           autocomplete="email" value="{{ old('email') }}">
                    <div class="field-error" id="emailError"></div>
                </div>

                {{-- Password --}}
                <div class="input-group-custom">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Password" autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePwd" title="Show/Hide Password">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                    <div class="field-error" id="passwordError"></div>
                </div>

                {{-- Options --}}
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotModal">
                        Forgot Password?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fa-solid fa-right-to-bracket" id="btnIcon"></i>
                    <span id="btnText">Sign In</span>
                </button>

            </form>
        </div>

        {{-- ── FOOTER ─────────────────────────────────── --}}
        <div class="login-footer">
            <i class="fa-solid fa-shield-halved me-1"></i>
            Secure ERP System &nbsp;|&nbsp; KIPS College Chakwal &nbsp;|&nbsp;
            &copy; {{ date('Y') }}
        </div>
    </div>
</div>

{{-- ── FORGOT PASSWORD MODAL ──────────────────── --}}
<div class="modal fade" id="forgotModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0" style="background:#1E3A5F; border-radius:16px 16px 0 0">
                <h6 class="modal-title text-white fw-600">
                    <i class="fa-solid fa-key me-2"></i>Reset Password
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Enter your email address and we'll send you a password reset link.
                </p>
                <div class="input-group-custom mb-3">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="forgotEmail" placeholder="Your Email Address">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-secondary btn-sm rounded-3" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i> Cancel
                </button>
                <button class="btn btn-sm rounded-3 text-white" id="sendResetBtn"
                        style="background:#1E3A5F">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send Reset Link
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── SCRIPTS ─────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const loginForm   = document.getElementById('loginForm');
    const loginBtn    = document.getElementById('loginBtn');
    const btnIcon     = document.getElementById('btnIcon');
    const btnText     = document.getElementById('btnText');
    const emailInput  = document.getElementById('email');
    const pwdInput    = document.getElementById('password');
    const emailError  = document.getElementById('emailError');
    const pwdError    = document.getElementById('passwordError');
    const togglePwd   = document.getElementById('togglePwd');
    const eyeIcon     = document.getElementById('eyeIcon');
    const lockoutAlert = document.getElementById('lockoutAlert');
    const lockoutMsg  = document.getElementById('lockoutMsg');
    const attemptsBar = document.getElementById('attemptsBar');
    const attemptsText = document.getElementById('attemptsText');

    let failedAttempts = 0;
    let lockTimer = null;

    // ── Show/Hide Password ──────────────────────────────────────
    togglePwd.addEventListener('click', function () {
        const type = pwdInput.getAttribute('type') === 'password' ? 'text' : 'password';
        pwdInput.setAttribute('type', type);
        eyeIcon.className = type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    });

    // ── Clear errors on input ───────────────────────────────────
    emailInput.addEventListener('input', () => clearError('email'));
    pwdInput.addEventListener('input',   () => clearError('password'));

    // ── Form Submit ─────────────────────────────────────────────
    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Client-side validation
        let valid = true;
        clearError('email'); clearError('password');

        if (!emailInput.value.trim()) {
            showError('email', 'Email address is required.'); valid = false;
        } else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
            showError('email', 'Please enter a valid email address.'); valid = false;
        }

        if (!pwdInput.value.trim()) {
            showError('password', 'Password is required.'); valid = false;
        }

        if (!valid) return;

        // Set loading state
        setLoading(true);

        try {
            const formData = new FormData(loginForm);
            const response = await fetch('{{ route("login.post") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // ✅ Success
                btnIcon.className = 'fa-solid fa-check';
                btnText.textContent = 'Redirecting...';
                loginBtn.style.background = 'linear-gradient(135deg, #27AE60, #1e8449)';

                await Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: `Welcome back! Redirecting to ${data.role} dashboard...`,
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true,
                });

                window.location.href = data.redirect;

            } else if (data.locked) {
                // 🔒 Account locked
                handleLockout(data.retry_in);

            } else {
                // ❌ Wrong credentials
                failedAttempts++;
                updateAttemptDots(failedAttempts);
                setLoading(false);

                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message,
                    confirmButtonColor: '#1E3A5F',
                    timer: 3000,
                    timerProgressBar: true,
                });

                // Shake animation
                loginForm.classList.add('shake');
                setTimeout(() => loginForm.classList.remove('shake'), 500);

                // Highlight fields
                emailInput.classList.add('is-invalid');
                pwdInput.classList.add('is-invalid');
                pwdInput.value = '';
            }

        } catch (err) {
            setLoading(false);
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to server. Please check your internet connection.',
                confirmButtonColor: '#1E3A5F',
            });
        }
    });

    // ── Helpers ─────────────────────────────────────────────────
    function setLoading(loading) {
        loginBtn.disabled = loading;
        if (loading) {
            btnIcon.className = 'fa-solid fa-spinner fa-spin';
            btnText.textContent = 'Signing In...';
        } else {
            btnIcon.className = 'fa-solid fa-right-to-bracket';
            btnText.textContent = 'Sign In';
            loginBtn.style.background = '';
        }
    }

    function showError(field, msg) {
        const input = field === 'email' ? emailInput : pwdInput;
        const error = field === 'email' ? emailError : pwdError;
        input.classList.add('is-invalid');
        error.textContent = msg;
        error.classList.add('show');
    }

    function clearError(field) {
        const input = field === 'email' ? emailInput : pwdInput;
        const error = field === 'email' ? emailError : pwdError;
        input.classList.remove('is-invalid');
        error.classList.remove('show');
    }

    function updateAttemptDots(attempts) {
        if (attempts <= 1) {
            attemptsBar.classList.add('show');
        }
        for (let i = 0; i < 5; i++) {
            const dot = document.getElementById('dot' + i);
            if (i < attempts) dot.classList.add('used');
            else dot.classList.remove('used');
        }
        const remaining = 5 - attempts;
        attemptsText.textContent = remaining > 0
            ? `${remaining} attempt(s) remaining before lockout`
            : 'Account will be locked on next failed attempt';
    }

    function handleLockout(seconds) {
        setLoading(false);
        lockoutAlert.classList.add('show');
        loginBtn.disabled = true;

        let remaining = seconds;
        const updateMsg = () => {
            const mins = Math.floor(remaining / 60);
            const secs = remaining % 60;
            lockoutMsg.textContent =
                `Too many failed attempts. Account locked for ${mins}m ${secs}s.`;
        };
        updateMsg();

        lockTimer = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(lockTimer);
                lockoutAlert.classList.remove('show');
                loginBtn.disabled = false;
                failedAttempts = 0;
                attemptsBar.classList.remove('show');
                for (let i = 0; i < 5; i++) {
                    document.getElementById('dot' + i).classList.remove('used');
                }
            } else {
                updateMsg();
            }
        }, 1000);
    }

    // ── Shake keyframe ──────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%,60%  { transform: translateX(-8px); }
            40%,80%  { transform: translateX(8px); }
        }
        .shake { animation: shake 0.5s ease; }
    `;
    document.head.appendChild(style);

    // ── Focus first field ───────────────────────────────────────
    emailInput.focus();
});
</script>
</body>
</html>
