<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — KIPS College Chakwal ERP</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            background: linear-gradient(135deg, #0d1f3c 0%, #1E3A5F 40%, #2C3E50 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .reset-wrapper { width: 100%; max-width: 420px; padding: 16px; }
        .reset-card {
            background: #fff; border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4); overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #1E3A5F, #2C3E50);
            padding: 30px 32px; text-align: center;
        }
        .reset-header i { font-size: 28px; color: #fff; margin-bottom: 8px; }
        .reset-header h1 { color: #fff; font-size: 17px; font-weight: 700; margin: 6px 0 0; }
        .reset-body { padding: 32px; }
        .input-group-custom { position: relative; margin-bottom: 16px; }
        .input-group-custom .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #adb5bd; font-size: 14px;
        }
        .input-group-custom input {
            width: 100%; padding: 12px 16px 12px 42px;
            border: 2px solid #e9ecef; border-radius: 10px; font-size: 14px;
            background: #fafafa; outline: none; transition: all .2s;
        }
        .input-group-custom input:focus {
            border-color: #1E3A5F; background: #fff;
            box-shadow: 0 0 0 4px rgba(30,58,95,0.08);
        }
        .strength-bar { height: 5px; border-radius: 4px; background: #e9ecef; margin: -8px 0 16px; overflow: hidden; }
        .strength-bar-fill { height: 100%; width: 0%; transition: all .3s; }
        .strength-weak .strength-bar-fill { width: 33%; background: #E74C3C; }
        .strength-medium .strength-bar-fill { width: 66%; background: #F39C12; }
        .strength-strong .strength-bar-fill { width: 100%; background: #27AE60; }
        .btn-reset {
            width: 100%; padding: 13px; border: none; border-radius: 10px;
            background: linear-gradient(135deg, #1E3A5F, #2C3E50); color: #fff;
            font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .btn-reset:disabled { opacity: .7; }
    </style>
</head>
<body>

<div class="reset-wrapper">
    <div class="reset-card">
        <div class="reset-header">
            <i class="fa-solid fa-key"></i>
            <h1>Set New Password</h1>
        </div>
        <div class="reset-body">
            <form id="resetForm">
                @csrf
                <input type="hidden" id="token" value="{{ $token }}">

                <div class="input-group-custom">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="email" value="{{ $email }}" placeholder="Email Address" readonly>
                </div>

                <div class="input-group-custom">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" placeholder="New Password">
                </div>
                <div class="strength-bar" id="strengthBar"><div class="strength-bar-fill"></div></div>

                <div class="input-group-custom">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="passwordConfirm" placeholder="Confirm New Password">
                </div>

                <button type="submit" class="btn-reset" id="resetBtn">
                    <i class="fa-solid fa-rotate-right me-1"></i> <span id="resetBtnText">Reset Password</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const pwdInput = document.getElementById('password');
const bar = document.getElementById('strengthBar');

pwdInput.addEventListener('input', function () {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    bar.className = 'strength-bar';
    if (score >= 4) bar.classList.add('strength-strong');
    else if (score >= 2) bar.classList.add('strength-medium');
    else if (v.length > 0) bar.classList.add('strength-weak');
});

document.getElementById('resetForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('passwordConfirm').value;

    if (password !== passwordConfirm) {
        Swal.fire({ icon: 'error', title: 'Passwords do not match', confirmButtonColor: '#1E3A5F' });
        return;
    }
    if (password.length < 8) {
        Swal.fire({ icon: 'warning', title: 'Password too short', text: 'Minimum 8 characters required.', confirmButtonColor: '#1E3A5F' });
        return;
    }

    const btn = document.getElementById('resetBtn');
    btn.disabled = true;
    document.getElementById('resetBtnText').textContent = 'Resetting...';

    try {
        const response = await fetch('{{ route("password.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                token: document.getElementById('token').value,
                email: document.getElementById('email').value,
                password: password,
                password_confirmation: passwordConfirm,
            })
        });
        const data = await response.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Password Reset!', text: data.message, confirmButtonColor: '#1E3A5F', timer: 2000, showConfirmButton: false });
            window.location.href = '{{ route("login") }}';
        } else {
            Swal.fire({ icon: 'error', title: 'Reset Failed', text: data.message, confirmButtonColor: '#1E3A5F' });
            btn.disabled = false;
            document.getElementById('resetBtnText').textContent = 'Reset Password';
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Connection Error', confirmButtonColor: '#1E3A5F' });
        btn.disabled = false;
        document.getElementById('resetBtnText').textContent = 'Reset Password';
    }
});
</script>
</body>
</html>
