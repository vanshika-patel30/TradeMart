<?php
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning"; 
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradeMart - Home</title>
    <link rel="stylesheet" href= "index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="logos/home_logo.png" alt="TradeMart Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="#" class="active">Home</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </nav>
            <div class="header-buttons">
                <button class="trading-btn" onclick="openModal()">Get Trading!</button>
                <button class="profile-btn" onclick="openModal()"><i class="bi bi-person-plus"></i></button>
            </div>
        </div>
    </header>
   
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <img src="logos/small_logo.png" alt="TradeMart Logo" class="modal-logo">
                <h2><?php echo $greeting; ?>!</h2>
            </div>
            <p>
                New here? <br>
                Already Part Of the <br>
                TradeMart Community?
            </p>
            <h3>Join South Africa's Trusted Marketplace for Clothing</h3>
            <button class="signup-btn">Sign Up</button>
            <button class="login-btn" >Log In</button>
        </div>
    </div>

    <section class="heading">
        <h1>Real People. Real Style. Real Deals.</h1>
    </section>

  <section class="main">
   <div class="slideshow">
        <div class="slide fade">
            <img src="featured/featured1.png" alt="Featured Products" style="width: 100%;">
        </div>
        <div class="slide fade">
            <img src="featured/featured2.png" alt="Featured Products" style="width: 100%;">
        </div>
        <div class="slide fade">
            <img src="featured/featured3.png" alt="Featured Products" style="width: 100%;">
        </div>
        <div class="slide fade">
            <img src="featured/featured4.png" alt="Featured Products" style="width: 100%;">
        </div>
        <div class="slide fade">
            <img src="featured/featured5.png" alt="Featured Products" style="width: 100%;">
        </div>
        <div class="slide fade">
            <img src="featured/featured6.png" alt="Featured Products" style="width: 100%;"> 
        </div>
    </div>

   <section class="welcome">
    <center>
        <h2><strong>WELCOME TO TRADEMART!</strong></h2>
        <p>TradeMart is South Africa's local e-commerce platfrom -perfect for the community!</p>
        <ul>
            <li>Discover unique local finds & support your neighbours.</li>
            <li>Easily sell your pre-loved clothes and connect with buyers nearby.</li>
        </ul>
    </center>
   </section>
</section>  

    <footer>
        <div class="footer-left">
            <img src= "logos/small_logo.png" alt="TradeMart Logo">
            <p><strong>TradeMart Market</strong><br>
            Head Office: Athlone, Cape Town, South Africa, 7780<br>
            Contact Details: +27 990 4567</p>
        </div>
        <div class="footer-center">
            <p><strong><i>Need Any Assistance?</i></strong></p>
            <ul>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">How To Buy</a></li>
                <li><a href="#">How To Sell</a></li>
                <li><a href="#">How Payment Works</a></li>
                <li><a href="#">Help Center</a></li>
                <li><a href="mailto:support@tradmart.co.za">support@trademart.co.za</a></li>
            </ul>
        </div>
        <div class="footer-right">
            <p><strong><i>Follow Us On Other Platforms</i></strong></p>
            <p>
                <i class="bi bi-instagram"></i> @TradeMartSA<br>
                <i class="bi bi-whatsapp"></i> /TradeMart Channel<br>
                <i class="bi bi-facebook"></i> @TradeMartSA
            </p>
        </div>
        <div class="footer-bottom">
            <p><strong>&copy; 2025 TradeMart. All rights reserved.</strong></p>
        </div>
    </footer>

    <script>
    $(document).ready(function () {
        let slideIndex = 0;
        const slides = $(".slide");
        const slideCount = slides.length;

        function showSlide(n) {
            slides.hide().removeClass("fade");
            slides.eq(n).fadeIn(600).addClass("fade");
        }

        function nextSlide() {
            slideIndex = (slideIndex + 1) % slideCount;
            showSlide(slideIndex);
        }

        showSlide(slideIndex);

        setInterval(nextSlide, 4000);


        function openModal() {
            $("#profileModal").fadeIn(200);
        }

        function closeModal() {
            $("#profileModal").fadeOut(200);
        }

        window.openModal = openModal;
        window.closeModal = closeModal;

        $(window).click(function (e) {
        if ($(e.target).is("#profileModal")) {
            closeModal();
        }
        });

        $(".signup-btn").click(function () {
            window.location.href = "signup.php";
        });
        $(".login-btn").click(function () {
            window.location.href = "login.php";
        });
    });
    </script>

</body>
</html>