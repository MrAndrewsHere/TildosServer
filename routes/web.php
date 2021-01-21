<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get("/media/{id}", "FilesController@getfile");
$router->get("/css", "FilesController@getcss");

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->group(['middleware' => ['role:admin']], function () use ($router)  {

    });
    $router->get('test', 'AdminController@test');
    $router->get('allProjects', 'AdminController@getAllProjects');
    $router->post('/DCRrequest', 'DomainRegistrationController@DCRrequest');
    $router->post('/DCRclose', 'DomainRegistrationController@DCRclose');
    $router->get('/DCRall', 'DomainRegistrationController@all');

    // Matches "/api/profile
    $router->get('profile', 'UserController@profile');
    $router->get('favicon/{domain}', function () use ($router){
        return response()->download('faviconold.ico','favicon');
    });
    $router->get('check', 'UserController@check');


    // Templates
    $router->get('/templates', "TemplateController@getAll");
    $router->get('/template/{id}', "TemplateController@get");
    $router->post('/template', "TemplateController@create");
    $router->put('/template/{id}', "TemplateController@update");
    $router->delete('/template/{id}', "TemplateController@delete");



    // Matches "/api/users/1
    //get one user by id
    $router->get('users/{id}', 'UserController@singleUser');
    $router->get('/projects', 'ProjectController@get_user_projects');
    $router->get('/project/{id}', 'ProjectController@get_project');
    $router->get('/projectsettings/{id}', 'ProjectController@projectsettings');
    $router->put("/project/{id}", "ProjectController@update_project");
    $router->post('/projects', 'ProjectController@create_project');
    // Matches "/api/users
    $router->get('users', 'UserController@allUsers');
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post("/pages", "PagesController@create");
    $router->post("/copypage", "PagesController@duplicate");
    $router->post("/publish", "PagesController@publish");
    $router->get("/pages", "PagesController@get");
    $router->get("/getpublicpage", "PublicController@get");
    $router->get("/pages/{id}", "PagesController@get");
    $router->put("/pages/{id}", "PagesController@update");
    $router->delete("/pages", "PagesController@delete");
    $router->put("/savepages/{id}", "PagesController@save");


    $router->post("/submit", "FormController@submit");
    $router->post('/searchcollaborators', 'ProjectController@searchcollaborators');
    $router->get("/stats", "StatsController@getStats");
    $router->put("/stats", "StatsController@changeStats");

    $router->get("/reports", "ReportsController@getReports");
    $router->post("/reports", "ReportsController@addReport");
    $router->delete("/reports/{id}", "ReportsController@deleteReport");


    $router->get("/FAQ", "FAQController@getFAQ");
    $router->post("/FAQ", "FAQController@addFAQ");
    $router->delete("/FAQ/{id}", "FAQController@deleteFAQ");

    //files
    $router->post("/files", "FilesController@uploadFile");
    $router->get("/files/{fileId}", "FilesController@getFile");



});
