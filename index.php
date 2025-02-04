<!DOCTYPE html>
<html lang="en">
<head>

    <title>ICYM Karate-Do | New User</title>
    <link rel="stylesheet" href="css/style.css"  id="style-resource-5">
    <script type="text/javascript" src="js/Script.js"></script>
    <link rel="stylesheet" href="css/dashMain.css">
    <link rel="stylesheet" type="text/css" href="css/entypo.css">
    <link href="a1style.css" type="text/css" rel="stylesheet">
    <script src="js/moment.min.js"></script>
	<script src="js/jquery-3.4.1.min.js"></script>
	
    <style>
    	.page-container .sidebar-menu #main-menu li#regis > a {
    	background-color: #2b303a;
    	color: #ffffff;
		}
       #boxx
	{
		width:220px;
	}
	 #space
{
line-height:0.5cm;
}
.home-button {
    position: fixed; /* Fixed positioning */
    bottom: 20px; /* Distance from the bottom of the viewport */
    right: 20px; /* Distance from the right of the viewport */
    background-color: #007bff; /* Bootstrap primary color */
    color: white; /* Text color */
    padding: 20px 25px; /* Padding around the button */
    border-radius: 5px; /* Rounded corners */
    text-decoration: none; /* No underline */
    font-size: 16px; /* Font size */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow effect */
    transition: background-color 0.3s; /* Transition effect */
}

.home-button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}
/* Container styling */
.a1-container {
  max-width: 600px;
  margin: auto;
  font-family: Arial, sans-serif;
}

.a1-card-8 {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

.a1-dark-gray {
  background-color: #333;
  color: #fff;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 15px;
}

/* Form Styling */
form {
  padding: 20px;
}

form label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
  color: #444;
}

form input[type="text"],
form input[type="date"],
form input[type="number"],
form input[type="email"],
form input[type="password"],
form input[type="file"],
form select {
  width: 100%;
  padding: 8px 12px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  transition: border 0.3s;
  box-sizing: border-box;
}

form input[type="text"]:focus,
form input[type="date"]:focus,
form input[type="number"]:focus,
form input[type="email"]:focus,
form input[type="password"]:focus,
form input[type="file"]:focus,
form select:focus {
  border-color: #007bff;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
  outline: none;
}

/* Fieldset Styling */
fieldset {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

legend {
  font-size: 1.2em;
  color: #333;
  padding: 0 10px;
  font-weight: bold;
}

/* Button Styling */
.a1-btn {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: bold;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.a1-btn:hover {
  background-color: #0056b3;
  transform: translateY(-2px);
}

.a1-btn:active {
  transform: translateY(1px);
}

.a1-blue {
  background-color: #007bff;
}

.a1-btn + .a1-btn {
  margin-left: 10px;
}
	/* Add a green text color and a checkmark when the requirements are right */
	.valid {
	color: green;
	}
	.valid:before {
	position: relative;
	left: -35px;
	content: "";
	}
	/* Add a red text color and an "x" when the requirements are wrong */
	.invalid {
	color: red;
	}
	.invalid:before {
	position: relative;
	left: -35px;
	content: "";
	}
	</style>

</head>
      <body class="page-body  page-fade" onload="collapseSidebar()">

    	<div class="" id="navbarcollapse">	

    		<div class="main-content">

		
        	<h1>Butiran Kehadiran Seminar</h1>

		<hr />
        

<div style="margin: 20px auto; padding: 20px; max-width: 100%;">


  <form id="form1" name="form1" method="post" action="new_submit.php" enctype="multipart/form-data" style="background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">

<!-- Personal Information Section -->
<fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
  <legend style="font-size: 20px; color: #333; font-weight: bold;">Personal Information</legend>
  <div>
    <div style="margin-bottom: 10px;">
      <label>ID:</label>
      <input type="text" name="m_id" value="<?php echo time(); ?>" readonly required style="width: 100%;" />
    </div>
    <div style="margin-bottom: 10px;">
      <label>Full Name:</label>
      <input type="text" name="fullName" required style="width: 100%;" />
    </div>
<div class="form-group">
  <label for="no_ic">IC Number</label>
  <input 
    type="text" 
    class="form-control" 
    name="no_ic" 
    id="no_ic" 
    placeholder="######-##-####" 
    autocomplete="off" 
    maxlength="14"
    required>
  <div id="ic-error" class="mt-2" style="display: none;"></div>
</div>

<script>
  (function() {
    const icInput = document.getElementById('no_ic');
    const errorDiv = document.getElementById('ic-error');
    let checkTimeout;
    let controller;

    icInput.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');

      if (value.length > 0) {
        value = formatICNumber(value);
      }

      e.target.value = value;
      const isComplete = value.replace(/-/g, '').length === 12;
      updateValidationUI(false);

      clearTimeout(checkTimeout);
      hideError();

      if (isComplete) {
        checkTimeout = debounceCheck(value.replace(/-/g, ''));
      }
    });

  function formatICNumber(value) {
    if (value.length <= 6) return value;
    if (value.length <= 8) return `${value.slice(0,6)}-${value.slice(6)}`;
    return `${value.slice(0,6)}-${value.slice(6,8)}-${value.slice(8,12)}`;
  }

    function updateValidationUI(isValid) {
      icInput.classList.remove('is-valid', 'is-invalid');
      if (isValid) {
        icInput.classList.add('is-valid');
      } else if (icInput.value.length > 0) {
        icInput.classList.add('is-invalid');
      }
    }

    const debounceCheck = debounce((icNumber) => {
      checkICExists(icNumber);
    }, 500);

    function debounce(func, delay) {
      let timeout;
      return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
      };
    }

    async function checkICExists(icNumber) {
      if (controller) {
        controller.abort();
      }
      controller = new AbortController();
      document.getElementById('ic-loading').style.display = 'inline-block';
      try {
        const response = await fetch('check_ic.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `ic=${encodeURIComponent(icNumber)}`,
          signal: controller.signal
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.error || 'Unable to verify IC number at this time.');
        }

        handleICResponse(data);
      } catch (error) {
        if (error.name === 'AbortError') return; // Request was aborted
        console.error('Validation Error:', error);
        showError('Failed to verify IC number. Please try again later.');
        icInput.setCustomValidity('Validation error');
      } finally {
        document.getElementById('ic-loading').style.display = 'none';
      }
    }

    function handleICResponse(data) {
      if (data.exists) {
        showError('This IC number is already registered!');
        icInput.setCustomValidity('IC number exists');
        updateValidationUI(false);
      } else {
        hideError();
        icInput.setCustomValidity('');
        updateValidationUI(true);
      }
    }

    function showError(message) {
      errorDiv.style.display = 'block';
      errorDiv.innerHTML = `
        <div class="alert alert-danger d-flex align-items-center" role="alert">
          <svg class="bi flex-shrink-0 me-2" width="24" height="24">
            <use xlink:href="#exclamation-triangle-fill"/>
          </svg>
          <div>${message}</div>
        </div>
      `;
    }

    function hideError() {
      errorDiv.style.display = 'none';
      errorDiv.innerHTML = '';
    }
  })();
</script>


<div class="form-group">
    <label for="mobile">Phone Number</label>
    <input 
        type="text" 
        class="form-control" 
        name="mobile" 
        id="mobile" 
        placeholder="###-########" 
        maxlength="12"
        autocomplete="off" 
        required>
</div>
<script>
    const phoneInput = document.getElementById('mobile');
    
    phoneInput.addEventListener('input', function(e) {
        // Remove all non-digits
        let value = e.target.value.replace(/\D/g, '');
        
        // Format the number
        if (value.length > 0) {
            if (value.length <= 3) {
                value = value;
            } else {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
        }
        
        // Update input value
        e.target.value = value;
        
        // Validation (optional)
        const isComplete = value.replace(/-/g, '').length === 11;
        e.target.style.borderColor = isComplete ? 'green' : '';
    });

    // Prevent non-numeric input (optional)
    phoneInput.addEventListener('keypress', function(e) {
        if (!/^\d$/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
            e.preventDefault();
        }
    });
</script>
  </div>
</fieldset>

<fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
  <legend style="font-size: 20px; color: #333; font-weight: bold;">Pengesahan melalui:</legend>
  
<div class="form-group">
  <p>Please select your favorite Web language:</p>
  <div style="display: inline-block; margin-right: 20px;">
    <input type="radio" id="html" name="fav_language" value="HTML">
    <label for="html">WhatsApp</label>
  </div>
  <div style="display: inline-block;">
    <input type="radio" id="css" name="fav_language" value="CSS">
    <label for="css">Walk-In</label>
  </div>
</div>

</fieldset>


<fieldset style="margin-bottom: 20px; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
  <legend style="font-size: 20px; color: #333; font-weight: bold;">Hadir Bersama Siapa:</legend>
  
  <div class="form-group" style="margin-top: 15px;">
    <div style="display: inline-block; margin-right: 30px;">
      <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike">
      <label for="vehicle1">Ibu-Bapa</label>
    </div>
    <div style="display: inline-block; margin-right: 30px;">
      <input type="checkbox" id="vehicle2" name="vehicle2" value="Car">
      <label for="vehicle2">Kawan / Saudara</label>
    </div>
    <div style="display: inline-block;">
      <input type="checkbox" id="vehicle3" name="vehicle3" value="Boat">
      <label for="vehicle3">Tiada</label>
    </div>
  </div>
</fieldset>

    <!-- Form Actions -->
    <div style="text-align: center; margin-top: 20px;">
      <button type="submit" class="a1-btn a1-blue">Submit</button>

    </div>
  </form>
</div>
        
			<?php include('footer.php'); ?>
						<a class="btn-sm px-4 py-3 d-flex home-button" style="background-color:#2a2e32" href="view_mem.php">Return Back</a>	
    	</div>

    </body>
</html>

