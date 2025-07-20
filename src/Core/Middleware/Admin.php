<?php

namespace src\Core\Middleware;

class Admin {

    public function handle() {
        // Logic for guest middleware
        if (!isset($_SESSION['access']) || $_SESSION['access'] !== 'admin') {
            header("Location: /login");
            exit();
        }
    }
}