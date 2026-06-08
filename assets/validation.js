
// STUDENT ATTENDANCE MANAGEMENT SYSTEM
// CLIENT-SIDE FORM VALIDATION (ALL FORMS)
// Purpose: Prevent invalid data before sending to PHP



// Show error message under an input field
console.log("validation.js is loaded");
function showError(input, message) {
    let error = input.parentElement.querySelector(".error-message");

    if (!error) {
        error = document.createElement("small");
        error.classList.add("error-message");
        error.style.color = "red";
        error.style.display = "block";
        error.style.marginTop = "5px";
        input.parentElement.appendChild(error);
    }

    error.innerText = message;
}

// Clear error message from input field
 
function clearError(input) {
    let error = input.parentElement.querySelector(".error-message");
    if (error) {
        error.innerText = "";
    }
}



// MAIN VALIDATION START

document.addEventListener("DOMContentLoaded", function () {

   
    // LOGIN FORM VALIDATION
    
    const loginForm = document.querySelector(".login-form");

    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {

            let username = document.getElementById("username");
            let password = document.getElementById("password");

            let isValid = true;

            clearError(username);
            clearError(password);

            // username check
            if (username.value.trim() === "") {
                showError(username, "Username is required");
                isValid = false;
            } else if (username.value.length < 3) {
                showError(username, "Username must be at least 3 characters");
                isValid = false;
            }

            // password check
            if (password.value.trim() === "") {
                showError(password, "Password is required");
                isValid = false;
            } else if (password.value.length < 4) {
                showError(password, "Password must be at least 4 characters");
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });
    }


    
    // REGISTER FORM VALIDATION
    
    const registerForm = document.querySelector(".login-form"); 
    // NOTE: your register form also uses class "login-form"

    if (registerForm && document.title.includes("Create Account")) {

        registerForm.addEventListener("submit", function (e) {

            let isValid = true;

            let username = document.getElementById("username");
            let password = document.getElementById("password");
            let confirmPassword = document.getElementById("confirm_password");
            let role = document.getElementById("role_id");

            // clear previous errors
            clearError(username);
            clearError(password);
            clearError(confirmPassword);
            clearError(role);

            // USERNAME VALIDATION
           
            if (username.value.trim() === "") {
                showError(username, "Username is required");
                isValid = false;
            } else if (username.value.length < 3) {
                showError(username, "Username must be at least 3 characters");
                isValid = false;
            }

           
            // PASSWORD VALIDATION
           
            if (password.value.trim() === "") {
                showError(password, "Password is required");
                isValid = false;
            } else if (password.value.length < 4) {
                showError(password, "Password must be at least 4 characters");
                isValid = false;
            }

           
            // CONFIRM PASSWORD CHECK
            
            if (confirmPassword.value.trim() === "") {
                showError(confirmPassword, "Please confirm password");
                isValid = false;
            } else if (confirmPassword.value !== password.value) {
                showError(confirmPassword, "Passwords do not match!");
                isValid = false;
            }

            
            // ROLE VALIDATION
            
            if (role.value === "") {
                showError(role, "Please select a role");
                isValid = false;
            }

            if (!isValid) e.preventDefault();
        });
    }

});