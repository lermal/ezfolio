<?php

use Illuminate\Support\Facades\Route;

//log viewer
Route::get('/admin/system-logs', ['\Rap2hpoutre\LaravelLogViewer\LogViewerController', 'index'])
    ->middleware('system-logs.access')
    ->name('system-logs');

Route::group(['prefix' => 'admin'], function () {
    Route::get('/{path?}', ['App\Http\Controllers\Admin\AdminController', 'app'])->where('path', '.*')->name('admin.app');
});


#region [frontend]

Route::get('/', ['App\Http\Controllers\Frontend\FrontendController', 'index'])->name('frontend');
Route::get('/projects/{slug}', ['App\Http\Controllers\Frontend\FrontendController', 'project'])
    ->where('slug', '[a-z0-9-]+')
    ->name('project');
Route::get('/services/{slug}', ['App\Http\Controllers\Frontend\FrontendController', 'service'])
    ->where('slug', '[a-z0-9-]+')
    ->name('service');
Route::get('/pixel-tracker', ['App\Http\Controllers\Frontend\FrontendController', 'pixelTracker'])->name('pixel-tracker');
Route::get('/sitemap.xml', ['App\Http\Controllers\Frontend\SitemapController', 'index'])->name('sitemap');
Route::get('/robots.txt', ['App\Http\Controllers\Frontend\SitemapController', 'robots'])->name('robots');
Route::post('/contact-me', ['App\Http\Controllers\Frontend\Api\GeneralController', 'store'])->name('contact-me');

#endregion
