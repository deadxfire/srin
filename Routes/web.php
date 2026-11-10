<?php

use Core\Link;

/*
|--------------------------------------------------------------------------
| Application Web Routes
|--------------------------------------------------------------------------
| Define all URL links and route mappings for your static website.
|
| Srin Link routing functions:
|     getlink('/', 'HomeController@index')->name('home');
|     getlink('/about', 'AboutController@index')->name('about');
|     getlink('/contact', 'ContactController@index')->name('contact');
|     postlink('/contact', 'ContactController@sendMail')->name('contact.send');
|
| Or fluent OOP style with Link facade:
|     Link::get('/portfolio', 'PortfolioController@index')->name('portfolio');
|     Link::group(['prefix' => '/admin', 'checkpoint' => 'auth'], function() {
|         getlink('/dashboard', 'AdminController@dashboard');
|     });
|
| Link to named routes in .srin.php templates:
|     <a href="[[ route('about') ]]">About Us</a>
|     <a href="[[ route('contact') ]]">Contact</a>
| Or in PHP:
|     <a href="<?= route('about') ?>">About Us</a>
|
*/

// Default Srin Engine Welcome Page (Pages/welcome.srin.php)
getlink('/', function() {
    return view('welcome', [
        'title' => 'Welcome to Srin Engine - Developed by Arindam Makar'
    ]);
})->name('home');

// Example API status route
getlink('/api/status', function() {
    json([
        'status' => 'online',
        'engine' => 'Srin Engine',
        'developer' => 'Arindam Makar',
        'github' => 'https://github.com/deadxfire',
        'timestamp' => time(),
    ]);
})->name('api.status');
