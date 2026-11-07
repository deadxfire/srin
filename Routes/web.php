<?php

use Core\Router;

/*
|--------------------------------------------------------------------------
| Application Web Routes
|--------------------------------------------------------------------------
| Define all URL links and route mappings for your static website.
|
| Define named routes by chaining ->name('route.name'):
|     Router::get('/', 'HomeController@index')->name('home');
|     Router::get('/about', 'AboutController@index')->name('about');
|     Router::get('/contact', 'ContactController@index')->name('contact');
|     Router::post('/contact', 'ContactController@sendMail')->name('contact.send');
|
| Link to named routes in .srin.php templates:
|     <a href="{{route('about')}}">About Us</a>
|     <a href="{{route('contact')}}">Contact</a>
| Or in PHP:
|     <a href="<?= route('about') ?>">About Us</a>
|
*/

// Default Srin Engine Welcome Page (Pages/welcome.srin.php)
Router::get('/', function() {
    return view('welcome', [
        'title' => 'Welcome to Srin Engine - Developed by Arindam Makar'
    ]);
})->name('home');

// Example API status route
Router::get('/api/status', function() {
    json([
        'status' => 'online',
        'engine' => 'Srin Engine',
        'developer' => 'Arindam Makar',
        'github' => 'https://github.com/deadxfire',
        'php_version' => PHP_VERSION,
        'timestamp' => time(),
    ]);
})->name('api.status');
