document.addEventListener('DOMContentLoaded', () => {
  // ambil elemen html yang dibutuhkan
  const loginForm = document.getElementById('login-form');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const emailError = document.getElementById('email-error');
  const passwordError = document.getElementById('password-error');

  // function cek format email dengan regex
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // function hapus status error dari input
  const clearError = (input, errorElement) => {
    input.classList.remove('input-error');
    errorElement.textContent = '';
  };

  // function pasang status error dan pesan
  const showError = (input, errorElement, message) => {
    input.classList.add('input-error');
    errorElement.textContent = message;
  };

  // hapus error saat user mengetik email
  emailInput.addEventListener('input', () => {
    clearError(emailInput, emailError);
  });

  // hapus error saat user mengetik password
  passwordInput.addEventListener('input', () => {
    clearError(passwordInput, passwordError);
  });

  // proses utama saat form login disubmit
  loginForm.addEventListener('submit', (e) => {
    // cegah halaman reload otomatis
    e.preventDefault();

    const emailValue = emailInput.value.trim();
    const passwordValue = passwordInput.value;
    let isValid = true;

    // bersihkan pesan error lama sebelum cek baru
    clearError(emailInput, emailError);
    clearError(passwordInput, passwordError);

    // validasi format dan isi email
    if (!emailValue) {
      showError(emailInput, emailError, 'Email wajib diisi.');
      isValid = false;
    } else if (!isValidEmail(emailValue)) {
      showError(emailInput, emailError, 'Format email tidak valid.');
      isValid = false;
    }

    // validasi password minimal 6 karakter
    if (!passwordValue) {
      showError(passwordInput, passwordError, 'Password wajib diisi.');
      isValid = false;
    } else if (passwordValue.length < 6) {
      showError(passwordInput, passwordError, 'Password minimal 6 karakter.');
      isValid = false;
    }

    // jika valid simpan ke localstorage dan beri alert
    if (isValid) {
      const userData = {
        email: emailValue,
        loggedInAt: new Date().toISOString()
      };
      localStorage.setItem('user', JSON.stringify(userData));

      console.log('Login berhasil', userData);
      alert('Login berhasil!');
    }
  });
});
