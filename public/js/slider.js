$(document).ready(function () {
    $(".hero-slider").slick({
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: true,
        nav:true,
        dots: true,
        fade: true,
        speed: 800,
        pauseOnHover: false,
    });
});


window.addEventListener("load", function () {
    $(".expert-slider").slick({
        arrows: true,
        dots: false,
        infinite: true,
        speed: 400,
        slidesToShow: 4,
        slidesToScroll: 1,
        lazyLoad: "ondemand", // 🔥 IMPORTANT
        responsive: [
            { breakpoint: 1200, settings: { slidesToShow: 3 } },
            { breakpoint: 768, settings: { slidesToShow: 2 } },
            { breakpoint: 500, settings: { slidesToShow: 1 } },
        ],
    });
});
