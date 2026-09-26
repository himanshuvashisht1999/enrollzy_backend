document.addEventListener('DOMContentLoaded', function () {
    const otpInputs = document.querySelectorAll('.otp-input');

    otpInputs.forEach((input, index) => {

        // Only numbers + auto move next
        input.addEventListener('input', (e) => {
            input.value = input.value.replace(/[^0-9]/g, '');

            if (input.value.length === 1 && otpInputs[index + 1]) {
                otpInputs[index + 1].focus();
            }
        });

        // Backspace = move previous
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '' && otpInputs[index - 1]) {
                otpInputs[index - 1].focus();
            }
        });

        // Paste full OTP support
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').replace(/\D/g, '');
            pasteData.split('').forEach((char, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = char;
                }
            });
            otpInputs[Math.min(pasteData.length, otpInputs.length) - 1]?.focus();
        });

    });
});