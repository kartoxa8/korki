const slider = document.querySelector('[data-slider]');

if (slider) {
    const slides = Array.from(slider.querySelectorAll('.slide'));
    const prev = slider.querySelector('[data-prev]');
    const next = slider.querySelector('[data-next]');
    let current = 0;

    const showSlide = (index) => {
        slides[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
    };

    prev.addEventListener('click', () => showSlide(current - 1));
    next.addEventListener('click', () => showSlide(current + 1));
    setInterval(() => showSlide(current + 1), 3000);
}
