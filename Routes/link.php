<?php

use Core\Link;

/*
|--------------------------------------------------------------------------
| Srin Engine - Route Links (Routes/link.php)
|--------------------------------------------------------------------------
| Developed by Arindam Makar
| GitHub: https://github.com/deadxfire
|
| Define all URL links and route mappings for your application.
| Srin provides expressive, native link functions and the fluent Link facade.
|
| Link Functions:
|   - getlink($path, $handler, ?$name)       -> Register a GET route link
|   - postlink($path, $handler, ?$name)      -> Register a POST route link
|   - putlink($path, $handler, ?$name)       -> Register a PUT route link
|   - patchlink($path, $handler, ?$name)     -> Register a PATCH route link
|   - deletelink($path, $handler, ?$name)    -> Register a DELETE route link
|   - optionslink($path, $handler, ?$name)   -> Register an OPTIONS route link
|   - anylink($path, $handler, ?$name)       -> Register a route link matching ANY HTTP verb
|   - grouplink($attributes, Closure $cb)    -> Group route links (prefix, checkpoint, name)
|
| CamelCase aliases: getLink(), postLink(), putLink(), patchLink(), deleteLink(),
| optionsLink(), anyLink(), groupLink().
|
| Fluent OOP Facade (Core\Link):
|   - Link::get($path, $handler, ?$name)
|   - Link::post($path, $handler, ?$name)
|   - Link::put($path, $handler, ?$name)
|   - Link::patch($path, $handler, ?$name)
|   - Link::delete($path, $handler, ?$name)
|   - Link::options($path, $handler, ?$name)
|   - Link::any($path, $handler, ?$name)
|   - Link::group($attributes, Closure $cb)
|
| Link to named routes in .srin.php templates:
|     <a href="[[ route('home') ]]">Home</a>
|     <a href="[[ route('articles.show', ['slug' => 'srin-guide']) ]]">Read</a>
| In PHP code:
|     $url = route('home');
|     $this->redirectToRoute('home');
|--------------------------------------------------------------------------
*/

// ============================================================================
// 1. ACTIVE APPLICATION ROUTE LINKS
// ============================================================================

// Default Welcome Page (Pages/welcome.srin.php)
getlink('/', function() {
    return view('welcome', [
        'title' => 'Welcome to Srin Engine - Developed by Arindam Makar'
    ]);
})->name('home');

// API Health / Status Endpoint
getlink('/api/status', function() {
    json([
        'status' => 'online',
        'engine' => 'Srin Engine',
        'developer' => 'Arindam Makar',
        'github' => 'https://github.com/deadxfire',
        'timestamp' => time(),
    ]);
})->name('api.status');


// ============================================================================
// 2. EXAMPLES: ALL HTTP VERB METHODS
// ============================================================================

// GET Link: Fetch resources or display views
// getlink('/about', 'HomeController@about')->name('about');
// getlink('/articles', 'ArticleController@index')->name('articles.index');
// getlink('/articles/{slug}', 'ArticleController@show')->name('articles.show');

// POST Link: Submit new form data or create records
// postlink('/contact', 'ContactController@sendMail')->name('contact.send');
// postlink('/articles', 'ArticleController@store')->name('articles.store');

// PUT Link: Complete update / replace of an existing resource
// putlink('/articles/{id}', 'ArticleController@replace')->name('articles.replace');

// PATCH Link: Partial modification of an existing resource
// patchlink('/articles/{id}', 'ArticleController@modify')->name('articles.modify');

// DELETE Link: Remove a resource
// deletelink('/articles/{id}', 'ArticleController@destroy')->name('articles.destroy');

// OPTIONS Link: Handle HTTP preflight requests / inspect allowed methods
// optionslink('/api/{endpoint}', function() {
//     header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
//     header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN');
//     exit;
// })->name('api.options');

// ANY Link: Matches ANY incoming HTTP method (GET, POST, PUT, DELETE, etc.)
// anylink('/webhook/{service}', 'WebhookController@handle')->name('webhook.handle');


// ============================================================================
// 3. EXAMPLES: CHECKPOINTS & ACCESS GATES
// ============================================================================

// Attach single or multiple checkpoints (e.g. 'auth', 'admin', 'guest')
// getlink('/dashboard', 'DashboardController@index')->name('dashboard')->checkpoint('auth');
// getlink('/admin/settings', 'AdminController@settings')->name('admin.settings')->checkpoint(['auth', 'admin']);


// ============================================================================
// 4. EXAMPLES: ROUTE LINK GROUPS (PREFIX + CHECKPOINTS)
// ============================================================================

// Group sharing URL prefix and authentication checkpoint
// grouplink(['prefix' => '/admin', 'checkpoint' => 'auth'], function () {
//     getlink('/', 'AdminController@dashboard')->name('admin.home');
//     getlink('/users', 'AdminController@users')->name('admin.users');
//     postlink('/users', 'AdminController@createUser')->name('admin.users.create');
//     putlink('/users/{id}', 'AdminController@updateUser')->name('admin.users.update');
//     deletelink('/users/{id}', 'AdminController@deleteUser')->name('admin.users.delete');
// });


// ============================================================================
// 5. EXAMPLES: FLUENT OOP LINK FACADE
// ============================================================================

// Link::get('/portfolio', 'PortfolioController@index')->name('portfolio');
// Link::post('/subscribe', 'NewsletterController@subscribe')->name('newsletter.subscribe');
// Link::group(['prefix' => '/api/v1'], function() {
//     Link::get('/items', 'ApiController@items');
//     Link::post('/items', 'ApiController@create');
//     Link::put('/items/{id}', 'ApiController@update');
//     Link::delete('/items/{id}', 'ApiController@remove');
//     Link::any('/ping', function() { json(['pong' => true]); });
// });
