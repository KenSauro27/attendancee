<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Auth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        accent: '#3B82F6',   /* soft blue */
                        lightBg: '#F9FAFB', /* off-white background */
                        cardBg: '#FFFFFF',  /* white cards */
                        textDark: '#111827',
                        textMuted: '#6B7280'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #F9FAFB;
        }

        .card {
            @apply bg-cardBg rounded-xl border border-gray-200 p-8 w-full max-w-md relative transition-all duration-300 ease-in-out;
        }

        .hidden-view {
            opacity: 0;
            pointer-events: none;
            transform: translateY(15px);
            position: absolute;
        }

        .active-view {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
            position: relative;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen px-4">

    <!-- CARD CONTAINER -->
    <div class="card">

        <!-- LOGIN -->
        <div id="login-view" class="active-view">
            <h2 class="text-2xl font-semibold text-textDark mb-6 text-center">Log In</h2>
            <form id="login-form" class="space-y-4" method="POST" action="core/user_handle.php">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent"
                    required>
                <input type="password" name="password" placeholder="Password"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent"
                    required>
                <button type="submit"
                    class="w-full bg-accent text-white font-medium py-3 rounded-lg hover:bg-blue-500 transition-colors">
                    Log In
                </button>
            </form>

            <p class="mt-6 text-sm text-textMuted text-center">Don’t have an account?
                <a href="#" id="show-register" class="text-accent hover:underline font-medium">Register</a>
            </p>
        </div>

        <!-- REGISTER -->
        <div id="register-view" class="hidden-view">
            <h2 class="text-2xl font-semibold text-textDark mb-6 text-center">Register</h2>
            <form id="register-form" class="space-y-4" method="POST" action="core/user_handle.php">
                <input type="hidden" name="action" value="register">
                <input type="text" name="name" placeholder="Full Name"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent"
                    required>
                <input type="email" name="email" placeholder="Email"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent"
                    required>
                <input type="password" name="password" placeholder="Password"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent"
                    required>
                <select name="role"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent">
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit"
                    class="w-full bg-accent text-white font-medium py-3 rounded-lg hover:bg-blue-500 transition-colors">
                    Register
                </button>
            </form>

            <p class="mt-6 text-sm text-textMuted text-center">Already have an account?
                <a href="#" id="show-login" class="text-accent hover:underline font-medium">Log In</a>
            </p>
        </div>

    </div>

    <script>
        const loginView = document.getElementById("login-view");
        const registerView = document.getElementById("register-view");
        const showRegister = document.getElementById("show-register");
        const showLogin = document.getElementById("show-login");

        function toggleView(show, hide) {
            hide.classList.remove("active-view");
            hide.classList.add("hidden-view");

            setTimeout(() => {
                show.classList.remove("hidden-view");
                show.classList.add("active-view");
            }, 100);
        }

        showRegister.addEventListener("click", (e) => {
            e.preventDefault();
            toggleView(registerView, loginView);
        });

        showLogin.addEventListener("click", (e) => {
            e.preventDefault();
            toggleView(loginView, registerView);
        });
    </script>

</body>

</html>
