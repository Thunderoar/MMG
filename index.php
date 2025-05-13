<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

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
  
  :root {
    --primary-color: #4361ee;
    --primary-light: #4895ef;
    --primary-dark: #3f37c9;
    --secondary-color: #4cc9f0;
    --success-color: #4ade80;
    --error-color: #f87171;
    --warning-color: #fbbf24;
    --text-color: #1f2937;
    --text-light: #6b7280;
    --bg-color: #f9fafb;
    --card-bg: #ffffff;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --transition: all 0.3s ease;
  }
  
  body {
    font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
    color: var(--text-color);
    line-height: 1.5;
    padding: 1.5rem;
    position: relative;
    overflow-x: hidden;
  }
  
  /* Background aesthetic elements */
  body::before,
  body::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    z-index: -1;
  }
  
  body::before {
    top: -5rem;
    right: -5rem;
    width: 40rem;
    height: 40rem;
    background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 30%, transparent 70%);
  }
  
  body::after {
    bottom: -8rem;
    left: -5rem;
    width: 30rem;
    height: 30rem;
    background: radial-gradient(circle, rgba(76, 201, 240, 0.1) 0%, rgba(76, 201, 240, 0.05) 40%, transparent 70%);
  }
  
  h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
    line-height: 1.25;
    margin-bottom: 1rem;
    color: var(--text-color);
  }
  
  h1 {
    font-size: 1.875rem;
    margin-bottom: 1.5rem;
  }
  
  /* Container Styles */
  .container {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin: 0 auto;
    padding: 1rem;
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
    border: none;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    z-index: 1;
  }
  
  /* Form section specific width */
  .form-section {
    flex: 1 1 600px;
    min-width: 600px;
  }
  
  /* Specific styling for table section to increase width */
  .table-section {
    flex: 3 1 800px;
    min-width: 800px;
  }
  
  .form-section::before,
  .table-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    z-index: 2;
  }
  
  .form-section:hover,
  .table-section:hover {
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(255, 255, 255, 0.9) inset;
    transform: translateY(-3px);
  }
  
  /* Table Styling */
  .table-section {
    overflow-x: auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
    background-color: var(--card-bg);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-md);
  }
  
  th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
  }
  
  th {
    background-color: var(--primary-light);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-right: 2rem;
    transition: background-color 0.3s;
  }
  
  th:hover {
    background-color: var(--primary-color);
  }
  
  th.sort-asc::after,
  th.sort-desc::after {
    content: '';
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
  }
  
  th.sort-asc::after {
    border-bottom: 5px solid white;
  }
  
  th.sort-desc::after {
    border-top: 5px solid white;
  }
  
  tr:last-child td {
    border-bottom: none;
  }
  
  tbody tr {
    transition: background-color 0.2s, transform 0.2s;
  }
  
  tbody tr:hover {
    background-color: rgba(67, 97, 238, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  }
  
  /* Attendance Fieldset Styles */
  .attendance-fieldset {
    margin-bottom: 2rem;
    padding: 1.75rem;
    border-radius: var(--radius-lg);
    border: none;
    background-color: rgba(255, 255, 255, 0.7);
    box-shadow: var(--shadow-sm), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    position: relative;
    overflow: hidden;
  }
  
  .attendance-fieldset::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(76, 201, 240, 0.05) 100%);
    z-index: -1;
  }
  
  .attendance-fieldset legend {
    font-size: 1.25rem;
    color: var(--primary-dark);
    font-weight: 600;
    padding: 0.5rem 1rem;
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    position: relative;
  }
  
  /* Form Group Styles */
  .form-group {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
  }
  
  /* For input groups, keep column layout */
  .form-group.input-group {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  /* Radio Container Styles */
  .radio-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    padding: 1.5rem 1.75rem;
    border-radius: var(--radius-lg);
    border: none;
    transition: var(--transition);
    width: 150px; /* Fixed width instead of min-width */
    position: relative;
    box-shadow: var(--shadow-sm);
    background-color: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    overflow: hidden;
  }
  
  .radio-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0) 100%);
    z-index: -1;
  }
  
  .radio-container::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
    transform: scaleX(0);
    transition: transform 0.5s ease;
  }
  
  .radio-container:hover {
    background-color: rgba(255, 255, 255, 0.9);
    transform: translateY(-5px);
    box-shadow: var(--shadow-md), 0 0 15px rgba(67, 97, 238, 0.15);
  }
  
  .radio-container:hover::after {
    transform: scaleX(1);
  }
  
  .radio-container input[type="radio"] {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
    accent-color: var(--primary-color);
    z-index: 2;
  }
  
  .radio-container input[type="radio"]:checked + img + span,
  .radio-container input[type="radio"]:checked ~ * {
    color: var(--primary-dark);
    font-weight: 600;
  }
  
  .radio-container:has(input[type="radio"]:checked) {
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: var(--shadow-md), 0 0 0 2px var(--primary-color), 0 0 20px rgba(67, 97, 238, 0.2);
  }
  
  .radio-container:has(input[type="radio"]:checked)::after {
    transform: scaleX(1);
    background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
    height: 4px;
  }
  
  .radio-container img {
    width: 3.75rem;
    height: 3.75rem;
    object-fit: contain;
    margin-bottom: 0.875rem;
    transition: var(--transition);
    filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.1));
  }
  
  .radio-container:hover img {
    transform: scale(1.15) translateY(-3px);
    filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.15));
  }
  
  .radio-container span {
    font-size: 1rem;
    color: var(--text-light);
    font-weight: 500;
    text-align: center;
    position: relative;
    transition: var(--transition);
  }
  
  /* Additional Options Styles */
  #sendiri-options {
    display: none;
    margin-top: 1.5rem;
    padding: 1.25rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background-color: rgba(67, 97, 238, 0.02);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
  }
  
  #sendiri-options label {
    display: block;
    font-size: 0.95rem;
    color: var(--text-light);
    margin-bottom: 0.75rem;
    transition: var(--transition);
  }
  
  #sendiri-options label:hover {
    color: var(--primary-dark);
  }
  
  #sendiri-options input[type="radio"] {
    margin-right: 0.5rem;
    accent-color: var(--primary-color);
    width: 1.125rem;
    height: 1.125rem;
  }
  
  /* Home Button Styling */
  .home-button {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    background-color: var(--primary-color);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 100;
  }
  
  .home-button:hover {
    background-color: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
  }
  
  .home-button:active {
    transform: translateY(-1px);
  }
  
  /* Button Styling */
  .a1-btn {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-md);
  }
  
  .a1-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }
  
  .a1-btn:active {
    transform: translateY(0);
    box-shadow: var(--shadow-sm);
  }
  
  .a1-btn:focus {
    outline: 2px solid rgba(67, 97, 238, 0.3);
    outline-offset: 2px;
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

  /* Form input styles */
  input[type="text"],
  input[type="email"],
  input[type="password"],
  input[type="number"],
  select,
  textarea {
    width: 100%;
    padding: 0.85rem 1.2rem;
    border: none;
    border-radius: var(--radius-lg);
    font-size: 1rem;
    color: var(--text-color);
    background-color: rgba(255, 255, 255, 0.8);
    transition: var(--transition);
    box-shadow: var(--shadow-sm), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
    outline: none;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    position: relative;
    overflow: hidden;
  }
  
  input[type="text"]::placeholder,
  input[type="email"]::placeholder,
  input[type="password"]::placeholder,
  input[type="number"]::placeholder,
  select::placeholder,
  textarea::placeholder {
    color: rgba(var(--text-light-rgb), 0.6);
  }

  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="password"]:focus,
  input[type="number"]:focus,
  select:focus,
  textarea:focus {
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 0 2px var(--primary-light), var(--shadow-md);
    transform: translateY(-2px);
  }
  
  /* Style for valid/invalid inputs */
  input.input-valid,
  textarea.input-valid {
    background-color: rgba(240, 255, 244, 0.95);
    box-shadow: 0 0 0 2px rgba(var(--success-rgb), 0.4), var(--shadow-sm);
  }
  
  input.input-invalid,
  textarea.input-invalid {
    background-color: rgba(255, 245, 245, 0.95);
    box-shadow: 0 0 0 2px rgba(var(--error-rgb), 0.4), var(--shadow-sm);
  }
  
  /* Custom textarea styles */
  textarea {
    min-height: 3rem;
    resize: vertical;
    line-height: 1.5;
    transition: all 0.3s ease;
    width: 100%;
  }
  
  textarea:focus {
    min-height: 5rem;
  }
  
  /* Style the resize handle */
  textarea::-webkit-resizer {
    border-width: 8px;
    border-style: solid;
    border-color: transparent var(--primary-light) var(--primary-light) transparent;
    background-color: transparent;
  }

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
    font-size: 0.95rem;
  }
  
  /* Input validation styles */
  .input-valid {
    border: 2px solid var(--success-color) !important;
    background-color: rgba(74, 222, 128, 0.05) !important;
  }
  
  .input-invalid {
    border: 2px solid var(--error-color) !important;
    background-color: rgba(248, 113, 113, 0.05) !important;
  }
  
  .input-checking {
    border: 2px solid var(--warning-color) !important;
    background-color: rgba(251, 191, 36, 0.05) !important;
  }

  /* Message styles */
  #ic-error, #mobile-validation, #fullname-validation, .error-message {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.375rem;
  }

  #ic-error.success, #mobile-validation.success, .error-message.success {
    color: var(--success-color) !important;
  }

  #ic-error.error, #mobile-validation.error, .error-message.error {
    color: var(--error-color) !important;
  }

  #ic-error.checking, #mobile-validation.checking, .error-message.checking {
    color: var(--warning-color) !important;
  }
  
  /* Priority badges */
  .priority-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius-md);
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
  }

  .priority-1 {
    background-color: var(--success-color);
    color: #065f46;
  }

  .priority-2 {
    background-color: var(--warning-color);
    color: #78350f;
  }

  .priority-3 {
    background-color: var(--primary-light);
    color: #1e3a8a;
  }

  .priority-4 {
    background-color: #94a3b8;
    color: #1e293b;
  }
  
  /* Priority info box */
  .priority-info {
    background-color: rgba(67, 97, 238, 0.03);
    padding: 1.25rem;
    margin: 1.25rem 0;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .priority-info h4 {
    margin: 0 0 0.75rem 0;
    color: var(--primary-dark);
    font-weight: 600;
    font-size: 1.125rem;
  }

  .priority-info ul {
    margin: 0;
    padding-left: 1.25rem;
  }

  .priority-info li {
    margin: 0.5rem 0;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .priority-info li .priority-badge {
    margin-right: 0.5rem;
  }

  body, html {
  margin-bottom: 0 !important;
  padding-bottom: 0 !important;
}

.main-content, .container {
  margin-bottom: 0 !important;
  padding-bottom: 0 !important;
}

/* Remove extra margin from the last child in the main content if any */
.main-content > *:last-child {
  margin-bottom: 0 !important;
}
</style>
</head>

<?php
require('header.php');
?>

<body>
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
  <div class="form-group input-group">
    <label for="fullName">Full Name:</label>
    <textarea name="fullName" id="fullName" title="Name cannot contain numbers" required style="resize: vertical; overflow: hidden; min-height: 3rem;"></textarea>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const fullNameInput = document.getElementById('fullName');
      
      // Auto-resize function
      function autoResize() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
      }
      
      // Initialize height
      autoResize.call(fullNameInput);
      
      // Attach events
      fullNameInput.addEventListener('input', function() {
        // Remove numbers as they are typed
        this.value = this.value.replace(/[0-9]/g, '');
        // Auto-resize
        autoResize.call(this);
      });
      
      fullNameInput.addEventListener('change', autoResize);
      fullNameInput.addEventListener('focus', autoResize);
      
      // Also resize on window resize in case of layout changes
      window.addEventListener('resize', function() {
        autoResize.call(fullNameInput);
      });
    });
  </script>
  <div class="form-group input-group">
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
                          // Remove all non-digit characters
                          let cleaned = value.replace(/\D/g, '');
                          
                          // Validate Malaysian IC number structure
                          const isValidFormat = /^\d{6}\d{2}\d{4}$/.test(cleaned);
                          
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
                          return fetch('check_noic.php', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/x-www-form-urlencoded'
                              },
                              body: `no_ic=${icNumber}`
                          })
                          .then(response => response.json())
                          .then(data => {
                              return data;
                          })
                          .catch(error => {
                              console.error('AJAX Error:', error);
                              return null;
                          });
                      }

                      // Update UI based on validation
                      function updateValidationUI(isValid, message, status) {
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
  <!-- <div class="form-group input-group">
    <label for="mobile">Phone Number</label>
    <input type="text" name="mobile" id="mobile" maxlength="12" autocomplete="off" required />
    <div id="mobile-validation" class="error-message" style="display: none;"></div>
  </div> -->
  <div class="form-group input-group">
              <label for="student_phone">Nombor Telefon Pelajar</label>
              <input type="text" name="student_phone" id="student_phone" maxlength="12" autocomplete="off" required />
              <div class="error-message" style="display: none;"></div>
            </div>

            <div class="form-group input-group">
              <label for="guardian_phone">Nombor Telefon Penjaga</label>
              <input type="text" name="guardian_phone" id="guardian_phone" maxlength="12" autocomplete="off" required />
              <div class="error-message" style="display: none;"></div>
            </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
  // Find all phone input fields
  const phoneInputs = document.querySelectorAll('#mobile, #student_phone, #guardian_phone');
  
  // Function to validate phone numbers
  function validatePhone(value) {
    // Remove any non-numeric characters
    const numericValue = value.replace(/\D/g, '');
    
    // Check if the length is between 10 and 11 digits
    if (numericValue.length >= 10 && numericValue.length <= 11) {
      return { isValid: true, message: '✓ Valid phone number', class: 'success' };
    } else {
      return { isValid: false, message: '✗ Phone number must be 10 or 11 digits', class: 'error' };
    }
  }
  
  // Function to format phone number as user types (XXX-XXXXXXX)
  function formatPhoneNumber(value) {
    // Remove non-numeric characters
    let numericValue = value.replace(/\D/g, '');
    
    // Apply formatting
    if (numericValue.length > 3) {
      return numericValue.slice(0, 3) + '-' + numericValue.slice(3);
    }
    
    return numericValue;
  }
  
  // Function to update UI based on validation results
  function updateValidationUI(input, isValid, message, status) {
    // Find the error message container
    let errorContainer = input.nextElementSibling;
    
    // If the next element isn't an error container, try to find it by ID
    if (!errorContainer || !errorContainer.classList.contains('error-message')) {
      errorContainer = document.getElementById(input.id + '-validation');
    }
    
    // Remove all existing status classes from input
    input.classList.remove('input-valid', 'input-invalid', 'input-checking');
    
    // Update the error message
    if (errorContainer) {
      errorContainer.textContent = message;
      errorContainer.style.display = 'block';
      errorContainer.classList.remove('success', 'error', 'checking');
      errorContainer.classList.add(status);
    }
    
    // Add appropriate class to input
    input.classList.add('input-' + status);
    
    // Update border color
    if (status === 'success') {
      input.style.borderColor = 'green';
    } else if (status === 'error') {
      input.style.borderColor = 'red';
    } else {
      input.style.borderColor = '';
    }
  }
  
  // Add validation to each phone input
  phoneInputs.forEach(function(input) {
    // Create error container if it doesn't exist
    let errorContainer = input.nextElementSibling;
    if (!errorContainer || !errorContainer.classList.contains('error-message')) {
      errorContainer = document.getElementById(input.id + '-validation');
      if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.id = input.id + '-validation';
        errorContainer.className = 'error-message';
        errorContainer.style.display = 'none';
        input.parentNode.insertBefore(errorContainer, input.nextSibling);
      }
    }
    
    // Input event - validate and format as user types
    input.addEventListener('input', function(e) {
      // Format the phone number
      const formattedValue = formatPhoneNumber(this.value);
      this.value = formattedValue;
      
      // Validate the input
      const result = validatePhone(this.value);
      updateValidationUI(this, result.isValid, result.message, result.class);
    });
    
    // Blur event - check if empty and validate
    input.addEventListener('blur', function() {
      if (this.value.trim() === '') {
        updateValidationUI(this, false, '✗ Phone number is required', 'error');
      } else {
        const result = validatePhone(this.value);
        updateValidationUI(this, result.isValid, result.message, result.class);
      }
    });
    
    // Prevent non-numeric input (except for allowed keys)
    input.addEventListener('keypress', function(e) {
      if (!/^\d$/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
        e.preventDefault();
      }
    });
    
    // Run initial validation if there's a value
    if (input.value.trim() !== '') {
      const result = validatePhone(input.value);
      updateValidationUI(input, result.isValid, result.message, result.class);
    }
  });
  
  // Add form submission validation
  const forms = document.querySelectorAll('form');
  forms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
      let isFormValid = true;
      
      // Check all phone inputs in this form
      const formPhoneInputs = form.querySelectorAll('#mobile, #student_phone, #guardian_phone');
      formPhoneInputs.forEach(function(input) {
        if (input.value.trim() === '') {
          updateValidationUI(input, false, '✗ Phone number is required', 'error');
          isFormValid = false;
        } else {
          const result = validatePhone(input.value);
          updateValidationUI(input, result.isValid, result.message, result.class);
          if (!result.isValid) {
            isFormValid = false;
          }
        }
      });
      
      // Prevent submission if any phone input is invalid
      if (!isFormValid) {
        e.preventDefault();
        return false;
      }
    });
  });
});
</script>

            <div class="form-group input-group">
              <label for="invited_officer">Pegawai Jemputan</label>
              <input type="text" name="invited_officer" id="invited_officer" maxlength="100" autocomplete="off" required />
            </div>
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
<fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
  <legend style="font-size: 20px; color: #333; font-weight: bold;">Hadir Bersama:</legend>
  <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin: 0.5rem 0; flex-wrap: nowrap;">
    <!-- Ibu-Bapa Option -->
    <label class="radio-container" style="width: 32%; max-width: 180px; min-width: 120px;">
      <input type="radio" id="ibubapa" name="hadir" value="Ibu Bapa">
      <img src="image/parents.png" alt="Ibu-Bapa" style="width: 50px; height: 50px; object-fit: contain;">
      <span>Ibu-Bapa</span>
    </label>

    <!-- Rakan / Saudara Option -->
    <label class="radio-container" style="width: 32%; max-width: 180px; min-width: 120px;">
      <input type="radio" id="rakanatausaudara" name="hadir" value="Rakan / Saudara">
      <img src="image/friends.svg" alt="Rakan / Saudara" style="width: 50px; height: 50px; object-fit: contain;">
      <span>Rakan / Saudara</span>
    </label>

    <!-- Sendiri Option -->
    <label class="radio-container" style="width: 32%; max-width: 180px; min-width: 120px;">
      <input type="radio" id="sendiri" name="hadir" value="Sendiri">
      <img src="image/sendiri.png" alt="Sendiri" style="width: 50px; height: 50px; object-fit: contain;">
      <span>Sendiri</span>
    </label>
  </div>
</fieldset>

<!-- Additional Options for 'Sendiri' -->
<div id="sendiri-options" class="form-group input-group" style="margin-top: 1rem; background-color: rgba(255, 255, 255, 0.7); padding: 1rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
  <div style="font-weight: 500; margin-bottom: 0.75rem; color: var(--primary-dark);">Keputusan:</div>
  <div style="display: flex; gap: 2rem;">
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
      <input type="radio" name="decision" value="Boleh membuat keputusan sendiri" style="accent-color: var(--primary-color);">
      <span>Boleh membuat keputusan sendiri</span>
    </label>
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
      <input type="radio" name="decision" value="Tidak boleh membuat keputusan sendiri" style="accent-color: var(--primary-color);">
      <span>Tidak boleh membuat keputusan sendiri</span>
    </label>
  </div>
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
<div style="text-align: center; margin-top: 0px;">
  <button type="submit" class="a1-btn">Submit</button>
</div>

          </form>
        </div>
        <!-- Data Table Section -->
        <div class="table-section">
          <h2>Senarai Kehadiran Seminar</h2>
          <?php
          // Get the next seminar info
          $today = date('Y-m-d');
          $seminarQuery = "SELECT zone, seminar_date, seminar_time, location 
                          FROM seminar_schedules 
                          WHERE seminar_date >= ? AND is_active = 1 
                          ORDER BY seminar_date ASC LIMIT 1";
          $stmt = mysqli_prepare($conn, $seminarQuery);
          mysqli_stmt_bind_param($stmt, "s", $today);
          mysqli_stmt_execute($stmt);
          $seminarResult = mysqli_stmt_get_result($stmt);
          $seminarInfo = mysqli_fetch_assoc($seminarResult);
          
          if ($seminarInfo): ?>
            <div class="upcoming-seminar" style="margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(76, 201, 240, 0.1) 100%); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
              <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Upcoming Seminar:</h3>
              <div style="display: grid; grid-template-columns: auto 1fr; gap: 1rem; align-items: center;">
                <strong>Zone:</strong>
                <span><?php echo htmlspecialchars($seminarInfo['zone']); ?></span>
                <strong>Date:</strong>
                <span><?php echo date('d M Y', strtotime($seminarInfo['seminar_date'])); ?></span>
                <strong>Time:</strong>
                <span><?php echo date('h:i A', strtotime($seminarInfo['seminar_time'])); ?></span>
                <strong>Location:</strong>
                <span><?php echo htmlspecialchars($seminarInfo['location']); ?></span>
              </div>
            </div>
          <?php endif; ?>

          <!-- Priority information box -->
          <div class="priority-info">
            <h4>Keutamaan</h4>
            <ul>
              <li><span class="priority-badge priority-1">Keutamaan Tinggi</span> Pelajar bersama ibu/bapa</li>
              <li><span class="priority-badge priority-2">Keutamaan Sederhana</span> Pelajar bersama saudara/rakan</li>
              <li><span class="priority-badge priority-3">Keutamaan Biasa</span> Pelajar sendiri (boleh membuat keputusan)</li>
              <li><span class="priority-badge priority-4">Keutamaan Rendah</span> Pelajar sendiri (tidak boleh membuat keputusan)</li>
            </ul>
          </div>

          <!-- Add a button to view past records above the table section -->
<div style="text-align:right; margin-bottom:1rem;">
  <button type="button" class="a1-btn" onclick="window.location.href='summary.php'">
    Lihat Rekod Lama
  </button>
</div>

          <table id="attendanceTable">
            <thead>
              <tr>
                <th data-sort="number">Giliran</th>
                <th data-sort="text">Status</th>
                <th data-sort="priority">Keutamaan</th>
                <th data-sort="text">Nama</th>
                <th data-sort="text">No. Kad Pengenalan</th>
                <th data-sort="text">Bersama Siapa</th>
                <th data-sort="none">Action</th>
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

              // Get current date in Malaysia timezone
              date_default_timezone_set("Asia/Kuala_Lumpur");
              $today = date('Y-m-d');
              
              // Get the next upcoming seminar
              $seminarQuery = "SELECT id, zone, seminar_date, seminar_time, location 
                              FROM seminar_schedules 
                              WHERE seminar_date >= ? AND is_active = 1 
                              ORDER BY seminar_date ASC, seminar_time ASC LIMIT 1";
              $stmt = mysqli_prepare($conn, $seminarQuery);
              mysqli_stmt_bind_param($stmt, "s", $today);
              mysqli_stmt_execute($stmt);
              $seminarResult = mysqli_stmt_get_result($stmt);
              $seminarInfo = mysqli_fetch_assoc($seminarResult);

              // Get records in priority order for the upcoming seminar
              $displayOrder = [];
              if ($seminarInfo) {
                  $sql = "SELECT NoIC 
                         FROM attendedstudent 
                         WHERE is_dealt = 0 
                         AND is_deleted = 0 
                         AND (seminar_id = ? OR seminar_id IS NULL)
                         ORDER BY priority ASC, DateofArrival ASC";
                  $stmt = mysqli_prepare($conn, $sql);
                  mysqli_stmt_bind_param($stmt, "i", $seminarInfo['id']);
                  mysqli_stmt_execute($stmt);
                  $result = mysqli_stmt_get_result($stmt);
                  
                  while ($row = mysqli_fetch_assoc($result)) {
                      $displayOrder[] = $row['NoIC'];
                  }
              }
              
              if (!empty($displayOrder)) {
                  // Convert array to comma-separated string of NoICs for the IN clause
                  $noicList = "'" . implode("','", array_map(function($noic) use ($conn) {
                      return mysqli_real_escape_string($conn, $noic);
                  }, $displayOrder)) . "'";
                  
                  // Get all records that match our order
                  $sql = "SELECT a.* 
                         FROM attendedstudent a 
                         WHERE a.NoIC IN ($noicList)";
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
                          
                          // Add an action button for all rows
                          echo "<td><button type='button' class='action-btn checkmark-btn' onclick='window.markDealt(\"" . htmlspecialchars($row['NoIC']) . "\")'><i class='checkmark-icon'>✓</i></button></td>";
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
          <!-- Button Styles -->
          <style>
            .action-btn {
              display: inline-flex;
              align-items: center;
              justify-content: center;
              padding: 0.625rem 1.25rem;
              border: none;
              border-radius: var(--radius-lg);
              background-color: rgba(255, 255, 255, 0.8);
              color: var(--primary-dark);
              font-weight: 600;
              font-size: 0.875rem;
              cursor: pointer;
              transition: all 0.3s ease;
              box-shadow: var(--shadow-sm), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
              backdrop-filter: blur(5px);
              -webkit-backdrop-filter: blur(5px);
              position: relative;
              overflow: hidden;
            }
            
            .checkmark-btn {
              width: 40px;
              height: 40px;
              padding: 0;
              border-radius: 50%;
              background-color: rgba(255, 255, 255, 0.9);
              color: var(--success-color);
              font-size: 1.25rem;
            }
            
            .checkmark-icon {
              font-style: normal;
              font-weight: bold;
              line-height: 1;
            }
            
            .action-btn::before {
              content: '';
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
              opacity: 0.1;
              z-index: -1;
            }
            
            .action-btn:hover {
              transform: translateY(-2px);
              box-shadow: var(--shadow-md), 0 0 0 1px var(--primary-light) inset;
              background-color: rgba(255, 255, 255, 0.95);
            }
            
            .checkmark-btn:hover {
              background-color: var(--success-color);
              color: white;
              box-shadow: var(--shadow-md);
            }
            
            .action-btn:active {
              transform: translateY(0);
              box-shadow: var(--shadow-sm) inset;
            }
            

          </style>
          
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
                            } catch (e) {
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
            
            // Table Sorting Functionality
            document.addEventListener('DOMContentLoaded', function() {
              const table = document.getElementById('attendanceTable');
              const headers = table.querySelectorAll('th');
              let currentSortCol = null;
              let currentSortOrder = 'asc';
              
              // Add click event listeners to sortable headers
              headers.forEach(header => {
                const sortType = header.getAttribute('data-sort');
                if (sortType && sortType !== 'none') {
                  header.addEventListener('click', () => {
                    // Reset all headers
                    headers.forEach(h => {
                      h.classList.remove('sort-asc', 'sort-desc');
                    });
                    
                    // Determine sort order
                    if (currentSortCol === header) {
                      currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                      currentSortCol = header;
                      currentSortOrder = 'asc';
                    });
                    
                    // Add visual indicator
                    header.classList.add(`sort-${currentSortOrder}`);
                    
                    // Sort the table
                    sortTable(table, headers.indexOf(header), sortType, currentSortOrder);
                  });
                }
              });
              
              // Function to sort table
              function sortTable(table, columnIndex, sortType, sortOrder) {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Sort rows based on cell content
                rows.sort((rowA, rowB) => {
                  const cellA = rowA.cells[columnIndex].textContent.trim();
                  const cellB = rowB.cells[columnIndex].textContent.trim();
                  
                  let comparison = 0;
                  
                  switch (sortType) {
                    case 'number':
                      comparison = parseInt(cellA) - parseInt(cellB);
                      break;
                    case 'priority':
                      // Extract priority number from class name (priority-X)
                      const priorityA = getPriorityValue(rowA.cells[columnIndex].querySelector('.priority-badge'));
                      const priorityB = getPriorityValue(rowB.cells[columnIndex].querySelector('.priority-badge'));
                      comparison = priorityA - priorityB;
                      break;
                    case 'boolean':
                      comparison = (cellA === 'Yes' ? 1 : 0) - (cellB === 'Yes' ? 1 : 0);
                      break;
                    case 'text':
                    default:
                      comparison = cellA.localeCompare(cellB, undefined, { sensitivity: 'base' });
                      break;
                  }
                  
                  return sortOrder === 'asc' ? comparison : -comparison;
                });
                
                // Re-append rows in sorted order
                rows.forEach(row => tbody.appendChild(row));
              }
              
              // Helper function to extract priority value
              function getPriorityValue(badgeElement) {
                if (!badgeElement) return 999; // Default high value if no badge
                
                const className = badgeElement.className;
                const match = className.match(/priority-(\d+)/);
                return match ? parseInt(match[1]) : 999;
              }
            });
          </script>
        </div>
      </div>

    </div>
  </div>
</body>
</html>
