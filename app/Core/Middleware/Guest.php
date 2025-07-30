<?php


namespace Core\Middleware;

class Guest {

    public function handle() {
        // Logic for guest middleware
        if (isset($_SESSION['access']) && $_SESSION['access'] === 'guest') {
            return;
        }
        
        // If the user is not logged in, redirect to login page
        if ($_SERVER['REQUEST_URI'] === '/login') {
            return;
        }

        header("Location: /login");
        exit();
    }
}