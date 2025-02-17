const container = document.querySelector('.container'); 
const registerButtons = document.querySelectorAll('.login-btn');
const loginButtons = document.querySelectorAll('.register-btn');

registerButtons.forEach(button => {
    button.addEventListener('click', () => {
        container.classList.add('active');
    });
});

loginButtons.forEach(button => {
    button.addEventListener('click', () => {
        container.classList.remove('active');
    });
});

// JavaScript untuk menangani ikon dropdown dan membuka select
document.addEventListener("DOMContentLoaded", function () {
    const selectElement = document.getElementById("role");
    const icon = document.querySelector(".input-box i");

    // Saat ikon ditekan, buka dropdown
    icon.addEventListener("click", function () {
        selectElement.size = selectElement.options.length; // Menampilkan semua opsi
        selectElement.classList.add("active");
        icon.classList.replace("bx-chevron-down", "bx-chevron-up"); // Ubah ikon jadi terbalik
    });

    // Saat memilih opsi atau kehilangan fokus, tutup dropdown
    selectElement.addEventListener("blur", function () {
        selectElement.size = 1; // Kembalikan ke tampilan biasa
        selectElement.classList.remove("active");
        icon.classList.replace("bx-chevron-up", "bx-chevron-down"); // Kembalikan ikon ke bawah
    });
});


const registerLink = document.querySelector('.login-link');
const loginLink = document.querySelector('.register-link');

registerLink.addEventListener('click', (e) => {
    e.preventDefault();
    container.classList.add('active'); // Pindah ke register
});

loginLink.addEventListener('click', (e) => {
    e.preventDefault();
    container.classList.remove('active'); // Pindah ke login
});



