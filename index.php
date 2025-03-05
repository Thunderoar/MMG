<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Add viewport meta tag for responsiveness -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Butiran Kehadiran Seminar</title>
  <!-- Styles -->
<style>
  /* Global Reset */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  
  body {
    font-family: Arial, sans-serif;
    background-color: #f8f8f8;
    padding: 20px;
  }
  
  /* Container Styling */
  .container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    #max-width: 1200px;
    margin: 0 auto;
  }
  
  /* Section Styling */
  .form-section,
  .table-section {
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    flex: 1 1 300px;
    min-width: 280px;
  }
  
  /* Table Styling */
  .table-section {
    overflow-x: auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  
  table th,
  table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  
  table th {
    background-color: #f1f1f1;
  }
  
  /* Attendance Fieldset Styles */
  .attendance-fieldset {
    margin-bottom: 20px;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ccc;
  }
  
  .attendance-fieldset legend {
    font-size: 20px;
    color: #333;
    font-weight: bold;
  }
  
  /* Form Group Styles */
  .form-group {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 10px;
  }
  
  /* Radio Container Styles */
  .radio-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    padding: 15px 25px;
    border-radius: 8px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    min-width: 120px;
    position: relative;
  }
  
  .radio-container:hover {
    border-color: #3b82f6;
    background-color: #f1f5f9;
    transform: translateY(-2px);
  }
  
  .radio-container input[type="radio"] {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
  }
  
  .radio-container input[type="radio"]:checked + img + span,
  .radio-container input[type="radio"]:checked ~ * {
    color: #2563eb;
  }
  
  .radio-container:has(input[type="radio"]:checked) {
    border-color: #2563eb;
    background-color: #A4CCFF;
  }
  
  .radio-container img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    margin-bottom: 8px;
  }
  
  .radio-container span {
    font-size: 14px;
    color: #4a5568;
    font-weight: 500;
  }
  
  /* Additional Options Styles */
  #sendiri-options {
    display: none;
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background-color: #f9fafb;
  }
  
  #sendiri-options label {
    display: block;
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 10px;
  }
  
  #sendiri-options input[type="radio"] {
    margin-right: 8px;
    accent-color: #2563eb;
  }
  
  /* Home Button Styling */
  .home-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #007bff;
    color: white;
    padding: 20px 25px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 16px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s;
  }
  
  .home-button:hover {
    background-color: #0056b3;
  }
  
  /* Button Styling */
  .a1-btn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  .a1-btn:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
  }
  
  .a1-btn:active {
    transform: translateY(0);
  }
  
  /* Responsive Adjustments */
  @media (max-width: 800px) {
    .container {
      flex-direction: column;
    }
  }
  
  /* Ensure Images are Responsive */
  img {
    max-width: 100%;
    height: auto;
  }
  
  :root {
    --primary-color: #4A90E2;
    --highlight-gradient: linear-gradient(135deg, #f2faff, #ecf7ff);
    --shadow-color: rgba(74, 144, 226, 0.5);
    --shadow-intense: rgba(74, 144, 226, 0.8);
    /* Custom cubic-bezier for a bouncy, iPhone-like effect */
    --transition-ease: cubic-bezier(0.175, 0.885, 0.320, 1.275);
  }

  /* Initially hide the 'sendiri-options' container */
  #sendiri-options {
    display: none;
    opacity: 0;
    transform: scale(0.97);
    transition: opacity 0.3s var(--transition-ease), transform 0.3s var(--transition-ease);
  }

  /* Reveal the container with a bouncy fade-in */
  #sendiri-options.show {
    display: block;
    opacity: 1;
    transform: scale(1);
  }

  /* Visual emphasis with a refined gradient, border, and shadow */
  #sendiri-options.highlight {
    border: 2px solid var(--primary-color);
    background: var(--highlight-gradient);
    box-shadow: 0 4px 12px var(--shadow-color);
    border-radius: 10px;
  }

  /* Bouncy pulse animation (1.5s cycle) */
  #sendiri-options.highlight.animate {
    animation: bouncePulse 2s infinite;
  }

  @keyframes bouncePulse {
    0% {
      transform: scale(1);
      box-shadow: 0 4px 12px var(--shadow-color);
    }
    40% {
      transform: scale(1.08);
      box-shadow: 0 6px 16px var(--shadow-intense);
    }
    60% {
      transform: scale(0.98);
      box-shadow: 0 4px 12px var(--shadow-color);
    }
    100% {
      transform: scale(1);
      box-shadow: 0 4px 12px var(--shadow-color);
    }
  }

  /* Input validation styles */
  .input-valid {
    border: 2px solid #4CAF50 !important;
    background-color: #f8fff8 !important;
  }
  
  .input-invalid {
    border: 2px solid #f44336 !important;
    background-color: #fff8f8 !important;
  }
  
  .input-checking {
    border: 2px solid #ff9800 !important;
    background-color: #fffbf5 !important;
  }

  /* Message styles */
  #ic-error {
    margin-top: 5px;
    font-size: 0.9em;
    transition: all 0.3s ease;
  }

  #ic-error.success {
    color: #4CAF50 !important;
  }

  #ic-error.error {
    color: #f44336 !important;
  }

  #ic-error.checking {
    color: #ff9800 !important;
  }
  
  /* Priority badges */
  .priority-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 500;
    margin-left: 8px;
  }

  .priority-1 {
    background-color: #4CAF50;
    color: white;
  }

  .priority-2 {
    background-color: #2196F3;
    color: white;
  }

  .priority-3 {
    background-color: #FF9800;
    color: white;
  }

  .priority-4 {
    background-color: #f44336;
    color: white;
  }

  /* Priority info box */
  .priority-info {
    background-color: #f5f5f5;
    border-left: 4px solid #2196F3;
    padding: 12px;
    margin: 16px 0;
    border-radius: 4px;
  }

  .priority-info h4 {
    margin: 0 0 8px 0;
    color: #333;
  }

  .priority-info ul {
    margin: 0;
    padding-left: 20px;
  }

  .priority-info li {
    margin: 4px 0;
    color: #666;
  }
</style>
</head>

<?php
require('header.php');
?>

<body onload="collapseSidebar()">
  <div id="navbarcollapse">
    <div class="main-content">
      <div class="container">
        <!-- Form Section -->
        <div class="form-section">
          <h1>Isi Butiran Kehadiran Seminar</h1>
          <form id="form1" name="form1" method="post" action="new_submit.php" enctype="multipart/form-data">
            <!-- Personal Information Section -->
            <fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
              <legend style="font-size: 20px; color: #333; font-weight: bold;">Info Pelajar</legend>
              <div>
  <div class="form-group">
    <label for="fullName">Full Name:</label>
    <input type="text" name="fullName" id="fullName" required />
  </div>
  <div class="form-group">
    <label for="no_ic">IC Number</label>
    <input type="text" name="no_ic" id="no_ic" autocomplete="off" maxlength="14" required />
    <div id="ic-error" class="error-message" style="display: none;"></div>
  </div>
                <script>
                  document.addEventListener('DOMContentLoaded', function() {
                      const icInput = document.getElementById('no_ic');
                      const icErrorDiv = document.getElementById('ic-error');
                      const form = icInput.closest('form');
                      let checkTimeout = null;
                      let lastValue = '';

                      // Function to format and validate the IC number
                      function formatAndValidateIC(value) {
                          console.log('Raw input:', value);
                          
                          // Remove all non-digit characters
                          let cleaned = value.replace(/\D/g, '');
                          console.log('Cleaned value:', cleaned);
                          
                          // Validate Malaysian IC number structure
                          const isValidFormat = /^\d{6}\d{2}\d{4}$/.test(cleaned);
                          console.log('Is valid format:', isValidFormat);
                          
                          // Format with hyphens
                          let formatted = cleaned;
                          if (cleaned.length > 6) {
                              formatted = cleaned.slice(0, 6) + '-' + cleaned.slice(6, 8) + (cleaned.length > 8 ? '-' + cleaned.slice(8) : '');
                          }
                          
                          return {
                              formatted: formatted,
                              isComplete: cleaned.length === 12 && isValidFormat,
                              cleaned: cleaned
                          };
                      }

                      // Check IC existence via AJAX
                      function checkICExistence(icNumber) {
                          console.log('Checking IC number:', icNumber);
                          
                          return fetch('check_noic.php', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/x-www-form-urlencoded'
                              },
                              body: `no_ic=${icNumber}`
                          })
                          .then(response => response.json())
                          .then(data => {
                              console.log('AJAX Success Response:', data);
                              return data;
                          })
                          .catch(error => {
                              console.error('AJAX Error:', error);
                              return null;
                          });
                      }

                      // Update UI based on validation
                      function updateValidationUI(isValid, message, status) {
                          console.log('Validation UI:', { isValid, message, status });
                          
                          // Remove all existing status classes
                          icInput.classList.remove('input-valid', 'input-invalid', 'input-checking');
                          icErrorDiv.classList.remove('success', 'error', 'checking');
                          
                          // Show error div and set message
                          icErrorDiv.style.display = 'block';
                          icErrorDiv.textContent = message;
                          
                          // Apply appropriate styling based on status
                          switch(status) {
                              case 'success':
                                  icInput.classList.add('input-valid');
                                  icErrorDiv.classList.add('success');
                                  break;
                              case 'error':
                                  icInput.classList.add('input-invalid');
                                  icErrorDiv.classList.add('error');
                                  break;
                              case 'checking':
                                  icInput.classList.add('input-checking');
                                  icErrorDiv.classList.add('checking');
                                  break;
                          }
                      }

                      // Handle backspace at dash positions
                      icInput.addEventListener('keydown', function(e) {
                          if (e.key === 'Backspace') {
                              const pos = this.selectionStart;
                              const value = this.value;
                              
                              // If cursor is right after a dash, delete the dash and the number before it
                              if (pos > 0 && value[pos - 1] === '-') {
                                  e.preventDefault();
                                  const before = value.slice(0, pos - 2);
                                  const after = value.slice(pos);
                                  this.value = before + after;
                                  this.setSelectionRange(pos - 2, pos - 2);
                                  
                                  // Trigger input event to update validation
                                  this.dispatchEvent(new Event('input'));
                              }
                          }
                      });

                      icInput.addEventListener('input', function(e) {
                          const result = formatAndValidateIC(e.target.value);
                          
                          // Only update the value if it's different to avoid cursor jumping
                          if (result.formatted !== e.target.value) {
                              const cursorPos = e.target.selectionStart;
                              const oldValue = e.target.value;
                              e.target.value = result.formatted;
                              
                              // Try to maintain cursor position
                              if (cursorPos === oldValue.length) {
                                  e.target.setSelectionRange(result.formatted.length, result.formatted.length);
                              } else {
                                  e.target.setSelectionRange(cursorPos, cursorPos);
                              }
                          }
                          
                          if (checkTimeout) {
                              clearTimeout(checkTimeout);
                          }
                          
                          if (!result.isComplete) {
                              updateValidationUI(false, '✗ Please enter a complete IC number (12 digits)', 'error');
                              return;
                          }
                          
                          checkTimeout = setTimeout(() => {
                              updateValidationUI(true, '⟳ Checking IC number availability...', 'checking');
                              
                              checkICExistence(result.cleaned)
                                  .then(function(response) {
                                      if (response.status === 'success') {
                                          if (response.exists) {
                                              updateValidationUI(false, '✗ This IC number has already been registered', 'error');
                                          } else {
                                              updateValidationUI(true, '✓ This IC number is available', 'success');
                                          }
                                      } else {
                                          updateValidationUI(false, '✗ Error: ' + (response.error || 'Unable to verify IC number'), 'error');
                                      }
                                  })
                                  .catch(function(error) {
                                      updateValidationUI(false, '✗ Network error. Please try again', 'error');
                                      console.error('AJAX Error:', error);
                                  });
                          }, 500);
                      });

                      form.addEventListener('submit', function(e) {
                          const result = formatAndValidateIC(icInput.value);
                          if (!result.isComplete) {
                              e.preventDefault();
                              updateValidationUI(false, '✗ Please enter a complete IC number (12 digits)', 'error');
                              return false;
                          }
                      });
                  });
                </script>
  <div class="form-group">
    <label for="mobile">Phone Number</label>
    <input type="text" name="mobile" id="mobile" maxlength="12" autocomplete="off" required />
  </div>
  
<style>
  .form-container {
    max-width: 500px !important;
    margin: 0 auto !important;
    padding: 20px !important;
    background-color: #f9f9f9 !important;
    border: 1px solid #ccc !important;
    border-radius: 8px !important;
    font-family: Arial, sans-serif !important;
  }

  .form-group {
    margin-bottom: 15px !important;
  }

  input[type="text"] {
    width: 100% !important;
    padding: 10px !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    font-size: 14px !important;
    box-sizing: border-box !important;
    transition: border-color 0.3s !important;
  }

  input[type="text"]:focus {
    border-color: #66afe9 !important;
    outline: none !important;
  }

  .error-message {
    color: red !important;
    font-size: 13px !important;
    margin-top: 5px !important;
  }
</style>  
                <script>
                  const phoneInput = document.getElementById('mobile');
                  phoneInput.addEventListener('input', function(e) {
                      let value = e.target.value.replace(/\D/g, '');
                      if (value.length > 0) {
                          if (value.length <= 3) {
                              value = value;
                          } else {
                              value = value.slice(0, 3) + '-' + value.slice(3);
                          }
                      }
                      e.target.value = value;
                      const isComplete = value.replace(/-/g, '').length === 11;
                      e.target.style.borderColor = isComplete ? 'green' : '';
                  });
                  phoneInput.addEventListener('keypress', function(e) {
                      if (!/^\d$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
                          e.preventDefault();
                      }
                  });
                </script>
              </div>
            </fieldset>

            <!-- Confirmation Fieldset -->
            <fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
              <legend style="font-size: 20px; color: #333; font-weight: bold;">Pengesahan melalui:</legend>
              <div class="form-group" style="display: flex; justify-content: center; gap: 40px; margin-top: 10px;">
                <label class="radio-container" style="display: flex; flex-direction: column; align-items: center; cursor: pointer; padding: 15px 25px; border-radius: 8px; border: 2px solid #e2e8f0; transition: all 0.3s ease; min-width: 120px; position: relative;">
                  <input type="radio" id="whatsapp" name="fav_language" value="WhatsApp" style="position: absolute; top: 10px; right: 10px; width: 18px; height: 18px; cursor: pointer;">
                  <img src="image/wsimg.png" alt="WhatsApp" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 8px;">
                  <span style="font-size: 14px; color: #4a5568; font-weight: 500;">WhatsApp</span>
                </label>
                <label class="radio-container" style="display: flex; flex-direction: column; align-items: center; cursor: pointer; padding: 15px 25px; border-radius: 8px; border: 2px solid #e2e8f0; transition: all 0.3s ease; min-width: 120px; position: relative;">
                  <input type="radio" id="walkin" name="fav_language" value="Walk-In" style="position: absolute; top: 10px; right: 10px; width: 18px; height: 18px; cursor: pointer;">
                  <img src="image/berjalan.webp" alt="Walk-In" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 8px;">
                  <span style="font-size: 14px; color: #4a5568; font-weight: 500;">Walk-In</span>
                </label>
              </div>
            </fieldset>

<!-- Attendance Fieldset -->
<fieldset class="attendance-fieldset">
  <legend>Hadir Bersama:</legend>
  <div class="form-group">
    <!-- Ibu-Bapa Option -->
    <label class="radio-container">
      <input type="radio" id="ibubapa" name="hadir" value="IbuBapa">
      <img src="image/parents.png" alt="Ibu-Bapa" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 8px;">
      <span>Ibu-Bapa</span>
    </label>

    <!-- Rakan / Saudara Option -->
    <label class="radio-container">
      <input type="radio" id="rakanatausaudara" name="hadir" value="Rakan/Saudara">
      <img src="image/friends.svg" alt="Rakan / Saudara" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 8px;">
      <span>Rakan / Saudara</span>
    </label>

    <!-- Sendiri Option -->
    <label class="radio-container">
      <input type="radio" id="sendiri" name="hadir" value="Sendiri">
      <img src="image/sendiri.png" alt="Sendiri" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 8px;">
      <span>Sendiri</span>
    </label>
  </div>
</fieldset>

<!-- Additional Options for 'Sendiri' -->
<div id="sendiri-options">
  <label>
    <input type="radio" name="decision" value="Boleh membuat keputusan sendiri">
    Boleh membuat keputusan sendiri
  </label>
  <label>
    <input type="radio" name="decision" value="Tidak boleh membuat keputusan sendiri">
    Tidak boleh membuat keputusan sendiri
  </label>
</div>

<!-- Script -->
<script>
// Global flag to indicate that the animation should stop gracefully.
let stopAnimationGracefully = false;
let removeAnimationTimeout;

// Function to toggle the display of 'sendiri-options' and apply visual effects.
function toggleRadioOptions() {
  const sendiriOption = document.getElementById('sendiri');
  const options = document.getElementById('sendiri-options');
  
  if (sendiriOption.checked) {
    // Show and start the animation
    options.classList.add('show', 'highlight', 'animate');
    
    // Smooth scroll into view.
    options.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Set focus to the first decision radio button.
    const firstDecisionRadio = options.querySelector('input[name="decision"]');
    if (firstDecisionRadio) firstDecisionRadio.focus();
    
    // Auto-remove the visual emphasis after 2000ms if no decision is made.
    removeAnimationTimeout = setTimeout(() => {
      if (!stopAnimationGracefully) {
        options.classList.remove('highlight', 'animate');
      }
    }, 2000);
    
  } else {
    // Hide the options when a different "hadir" radio is selected.
    options.classList.remove('show');
    
    // Reset decision radio buttons.
    document.getElementsByName('decision').forEach((radio) => radio.checked = false);
    
    clearTimeout(removeAnimationTimeout);
    stopAnimationGracefully = false;
  }
}

// Attach toggle function to all 'hadir' radio buttons.
document.getElementsByName('hadir').forEach((radio) => {
  radio.addEventListener('change', toggleRadioOptions);
});

// When a decision is selected, set the flag to stop the animation gracefully.
document.querySelectorAll('input[name="decision"]').forEach((radio) => {
  radio.addEventListener('change', () => {
    stopAnimationGracefully = true;
    clearTimeout(removeAnimationTimeout);
    // The animationiteration event will remove the classes at the end of the cycle.
  });
});

// Listen for each iteration of the bouncePulse animation.
const sendiriOptions = document.getElementById('sendiri-options');
sendiriOptions.addEventListener('animationiteration', () => {
  if (stopAnimationGracefully) {
    sendiriOptions.classList.remove('highlight', 'animate');
    stopAnimationGracefully = false; // Reset the flag.
  }
});

// Run on page load in case 'Sendiri' is pre-selected.
window.addEventListener('DOMContentLoaded', toggleRadioOptions);

</script>


            
            <!-- Form Actions -->
<div style="text-align: center; margin-top: 20px;">
  <button type="submit" class="a1-btn">Submit</button>
</div>

          </form>
        </div>
        <!-- Data Table Section -->
        <div class="table-section">
          <h2>Senarai Kehadiran Seminar</h2>
          <!-- Add priority information box -->
          <div class="priority-info">
            <h4>Queue Priority System</h4>
            <ul>
              <li><span class="priority-badge priority-1">Keutamaan Tinggi</span> Pelajar bersama ibu/bapa</li>
              <li><span class="priority-badge priority-2">Keutamaan Sederhana</span> Pelajar bersama saudara/rakan</li>
              <li><span class="priority-badge priority-3">Keutamaan Biasa</span> Pelajar sendiri (boleh membuat keputusan)</li>
              <li><span class="priority-badge priority-4">Keutamaan Rendah</span> Pelajar sendiri (tidak boleh membuat keputusan)</li>
            </ul>
          </div>
          <table>
            <thead>
              <tr>
                <th>Giliran</th>
                <th>Status</th>
                <th>Keutamaan</th>
                <th>Nama</th>
                <th>No. Kad Pengenalan</th>
                <th>Bersama Siapa</th>
                <th>Boleh Membuat Keputusan</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              require_once 'include/sequence_manager.php';
              require_once 'include/priority_helper.php';
              
              // --- Database connection setup ---
              $host     = "localhost";
              $username = "root";
              $password = "";
              $database = "mmg_db";
              
              // Create connection using mysqli
              $conn = new mysqli($host, $username, $password, $database);
              if ($conn->connect_error) {
                  die("Connection failed: " . $conn->connect_error);
              }
              
              // Get records in priority order
              $displayOrder = getDisplayOrder();
              
              if (!empty($displayOrder)) {
                  // Convert array to comma-separated string of NoICs for the IN clause
                  $noicList = "'" . implode("','", array_map(function($noic) use ($conn) {
                      return mysqli_real_escape_string($conn, $noic);
                  }, $displayOrder)) . "'";
                  
                  // Get all records that match our order
                  $sql = "SELECT * FROM attendedstudent WHERE NoIC IN ($noicList)";
                  $result = $conn->query($sql);
                  
                  // Create an array to store records by NoIC for quick lookup
                  $records = [];
                  while ($row = $result->fetch_assoc()) {
                      $records[$row['NoIC']] = $row;
                  }
                  
                  // Display records in the correct order
                  $displayNumber = 0;
                  foreach ($displayOrder as $noic) {
                      if (isset($records[$noic])) {
                          $row = $records[$noic];
                          $displayNumber++;
                          
                          echo "<tr>";
                          // Display sequential number
                          echo "<td>" . $displayNumber . "</td>";
                          
                          // Display the Pengesahan column with an icon and label
                          $fromWhere = $row['FromWhere'];
                          if ($fromWhere === 'WhatsApp') {
                              echo "<td>
                                      <img src='image/wsimg.png' alt='WhatsApp' style='width:50px; height:50px; object-fit:contain; margin-bottom:8px;'><br>
                                      <span style='font-size:14px; color:#4a5568; font-weight:500;'>WhatsApp</span>
                                    </td>";
                          } elseif ($fromWhere === 'Walk-In') {
                              echo "<td>
                                      <img src='image/berjalan.webp' alt='Walk-In' style='width:50px; height:50px; object-fit:contain; margin-bottom:8px;'><br>
                                      <span style='font-size:14px; color:#4a5568; font-weight:500;'>Walk-In</span>
                                    </td>";
                          } else {
                              echo "<td>" . htmlspecialchars($fromWhere) . "</td>";
                          }
                          
                          // Priority info
                          $priorityInfo = getPriorityInfo($row['WithWho'], $row['canMakeDecision'], $row['FromWhere']);
                          echo "<td><span class='priority-badge priority-" . $priorityInfo['priority'] . "'>" . 
                               $priorityInfo['description'] . "</span></td>";
                          echo "<td>" . htmlspecialchars($row['fullName']) . "</td>";
                          echo "<td>" . $row['NoIC_Display'] . "</td>";
                          echo "<td>" . htmlspecialchars($row['WithWho']) . "</td>";
                          echo "<td>" . ($row['canMakeDecision'] ? 'Yes' : 'No') . "</td>";
                          
                          // Add an action button only for the first row
                          if ($displayNumber === 1) {
                              echo "<td><button type='button' onclick='markDealt(\"" . htmlspecialchars($row['NoIC']) . "\")'>Mark as Done</button></td>";
                          } else {
                              echo "<td></td>";
                          }
                          echo "</tr>";
                      }
                  }
              } else {
                  echo "<tr><td colspan='8'>Tiada rekod dijumpai.</td></tr>";
              }
              
              $conn->close();
              ?>
            </tbody>
          </table>
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              window.markDealt = function(noIC) {
                if (confirm("Are you sure you want to mark this record as dealt with?")) {
                  var xhr = new XMLHttpRequest();
                  xhr.open('POST', 'mark_dealt.php', true);
                  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                  
                  xhr.onload = function() {
                    if (xhr.status === 200) {
                      try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.status === 'success') {
                          alert(result.message);
                          location.reload();
                        } else {
                          alert("Error: " + result.message);
                        }
                      } catch(e) {
                        alert("Error processing response");
                        console.error(e, xhr.responseText);
                      }
                    } else {
                      alert("Error marking record: " + xhr.statusText);
                    }
                  };
                  
                  xhr.onerror = function() {
                    alert("Error marking record. Please try again.");
                  };
                  
                  xhr.send('noIC=' + encodeURIComponent(noIC));
                }
              };
            });
          </script>
        </div>
      </div>
      <!-- Footer and Return Button -->
      <?php include('footer.php'); ?>
      <a class="btn-sm px-4 py-3 d-flex home-button" style="background-color:#2a2e32" href="view_mem.php">Return Back</a>
    </div>
  </div>
</body>
</html>
