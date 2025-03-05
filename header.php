<?php
require 'include/db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>ICYM Karate-Do &mdash; Colorlib Website Template</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Fonts and Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:200,300,400,700,900|Oswald:400,700"> 
  <link rel="stylesheet" href="fonts/icomoon/style.css">

  <!-- CSS Files -->
  <!-- <link rel="stylesheet" href="css/bootstrap.min.css">  causes problem with scaling --> 
  <link rel="stylesheet" href="css/jquery.fancybox.min.css">
  <link rel="stylesheet" href="css/jquery-ui.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/owl.theme.default.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">
  <link rel="stylesheet" href="css/aos.css">
  <link rel="stylesheet" href="css/homepagestyle.css">
  <!-- Optional additional bootstrap -->
  <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> 
  causes problem with scaling --> 

  <!-- JS Files (if needed in the header) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <style>
    /* General Link and Paragraph Styles */
    a:not(.not) {
      color: #a9c9fc !important;
    }
    a:not(.not):hover {
      color: white !important;
    }
    p {
      text-indent: 20px;
    }
    p:hover {
      color: #00358a !important;
    }
    /* Section Styles */
    .site-section {
      background-color: #ffffff; 
      padding: 40px; 
      border-radius: 10px; 
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    }
    .section-title {
      font-size: 2.5rem; 
      font-weight: bold; 
      color: #343a40; 
      margin-bottom: 20px; 
      text-align: center; 
    }
    /* Image Effects */
    img:not(.logo) {
      transition: transform 0.3s ease, box-shadow 0.3s ease; 
      border-radius: 10px; 
      width: 100%; 
      height: auto; 
    }
    img:not(.logo):hover {
      transform: scale(1.05); 
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
    }
    /* Menu Styles */
    .menu {
      list-style-type: none; 
      padding: 0; 
      margin: 0; 
      display: flex;
      align-items: center;
    }
    .menu li {
      margin-right: 20px; 
    }
    .menu a {
      text-decoration: none; 
      padding: 10px 15px; 
      color: #343a40; 
      transition: transform 0.3s ease !important; 
    }
    .menu a:hover {
      transform: scale(1.1) !important; 
    }
    /* Dropdown Styles */
    .dropdown {
      position: relative; 
      width: 230px; 
      filter: url(#goo);
    }
    .dropdown__face,
    .dropdown__items {
      background-color: #fff; 
      padding: 20px; 
      border-radius: 25px; 
    }
    .dropdown__face {
      display: block; 
      position: relative; 
      cursor: pointer; 
    }
    .dropdown__items {
      margin: 0;
      position: absolute;
      right: 0;
      top: 100%; /* Below the button */
      list-style: none;
      display: flex;
      flex-direction: column; /* Stack vertically */
      visibility: hidden;
      z-index: -1;
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.93, 0.88, 0.1, 0.8);
      background-color: #fff;
      border-radius: 5px;
    }
    .dropdown__items.visible {
      visibility: visible;
      opacity: 1;
      z-index: 1;
    }
    .dropdown__items li {
      padding: 10px 15px;
      white-space: nowrap;
    }
    .dropdown__items li:hover {
      background-color: #f0f0f0;
    }
    .dropdown__arrow {
      border-bottom: 2px solid #000; 
      border-right: 2px solid #000; 
      position: absolute; 
      top: 50%; 
      right: 30px; 
      width: 10px; 
      height: 10px; 
      transform: rotate(45deg) translateY(-50%); 
      transform-origin: right; 
    }
    /* Fixed Header Styles */
    header.site-navbar {
      background-color: #00003c;
      height: 80px;  /* Fixed height */
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      padding: 0 20px;
    }
    header.site-navbar .container {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .site-logo {
      display: flex;
      align-items: center;
    }
    .site-logo img.logo {
      height: 50px;
      margin-right: 10px;
    }
    .site-logo a {
      font-size: 15px;
      color: #a9c9fc !important;
      text-decoration: none;
    }
    nav.site-navigation .menu {
      margin: 0;
      padding: 0;
    }
    nav.site-navigation .menu li a {
      color: #fff;
      text-decoration: none;
      padding: 10px 15px;
    }
    nav.site-navigation .menu li a:hover {
      color: #a9c9fc;
    }
    /* Remove body centering so the header stays at the top */
    body {
      font-family: "Lato", Arial, sans-serif;
      margin: 0;
      padding-top: 80px; /* To prevent header overlap with content */
      background-image: linear-gradient(140deg, #e2e2e2, #cdcdcd);
    }
    * {
      box-sizing: border-box;
    }
    svg {
      display: none;
    }
  </style>
</head>
<body>
  <!-- Fixed Header -->
  <header class="site-navbar">
    <div class="container">
      <div class="site-logo">
        <img src="image/magmalogo.png" alt="Logo" class="logo" />
        <a href="index.php">Magma TRD Resources</a>
      </div>
      <nav class="site-navigation">
        <ul class="menu">
          <li><a href="index.php">Home</a></li>
          <li><a href="gallery.php">Gallery</a></li>
          <li><a href="events.php">Events</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact</a></li>
          <!-- User Dropdown -->
          <li class="nav-item">
            <div class="user-info d-flex align-items-center">
              <!--<button class="btn btn-primary btn-sm px-2 py-1 d-flex align-items-center" id="dropdownButton">
                <img src="dashboard/member/path/to/profile.jpg" alt="Profile Picture" class="img-fluid" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;"/>
                <span class="username ml-2">JohnDoe</span>
              </button>
              <!-- Custom Dropdown -->
               <!-- <ul class="dropdown__items" id="dropdownItems">
                <li><a class="not" href="dashboard/member/">Dashboard</a></li>
                <li><a class="not" href="dashboard/member/logout.php">Log Out</a></li>
              </ul> -->
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- (Optional) SVG filter definition for dropdown effects -->
  <svg>
    <filter id="goo">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"/>
      <feColorMatrix in="blur" type="matrix"
                     values="1 0 0 0 0  
                             0 1 0 0 0  
                             0 0 1 0 0  
                             0 0 0 18 -7" result="goo"/>
      <feBlend in="SourceGraphic" in2="goo"/>
    </filter>
  </svg>

  <!-- Page content goes here -->
  <!-- <div class="site-section">
    <!-- Your content -->
    <!--<h2 class="section-title">Welcome to ICYM Karate-Do</h2>
    <p>Your main content goes here.</p>
  </div> -->

  <!-- Dropdown Toggle Script -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const dropdownButton = document.getElementById('dropdownButton');
      const dropdownItems = document.getElementById('dropdownItems');

      dropdownButton.addEventListener('click', function() {
        dropdownItems.classList.toggle('visible');
      });
    });
  </script>
</body>
</html>
