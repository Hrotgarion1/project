<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/employees', function () {
    return Inertia::render('Landing/LandingEmployees');
})->name('landing.employees');

Route::get('/students', function () {
    return Inertia::render('Landing/LandingStudents');
})->name('landing.students');

Route::get('/study-centers', function () {
    return Inertia::render('Landing/LandingStudyCenter');
})->name('landing.studycenters');

Route::get('/companies', function () {
    return Inertia::render('Landing/LandingCompanies');
})->name('landing.companies');

Route::get('/social-entities', function () {
    return Inertia::render('Landing/LandingSocialEntities');
})->name('landing.socialentities');

Route::get('/online-courses', function () {
    return Inertia::render('Landing/LandingOnlineCourses');
})->name('landing.onlinecourses');

Route::get('/self-employed', function () {
    return Inertia::render('Landing/LandingSelfEmployed');
})->name('landing.selfemployed');

Route::get('/entrepreneurs', function () {
    return Inertia::render('Landing/LandingEntrepreneurs');
})->name('landing.entrepreneurs');

Route::get('/investors', function () {
    return Inertia::render('Landing/LandingInvestors');
})->name('landing.investors');

Route::get('/recruiters', function () {
    return Inertia::render('Landing/LandingRecruiters');
})->name('landing.recruiters');

Route::get('/about', function () {
    return Inertia::render('Landing/LandingAbout');
})->name('landing.about');

Route::get('/help-center', function () {
    return Inertia::render('Landing/LandingHelpCenter');
})->name('landing.help-center');

Route::get('/blog', function () {
    return Inertia::render('Landing/LandingBlog');
})->name('landing.blog');

Route::get('/where_we_are', function () {
    return Inertia::render('Landing/LandingCountries');
})->name('landing.countries');

Route::get('/press', function () {
    return Inertia::render('Landing/LandingPress');
})->name('landing.press');

Route::get('/work-with-us', function () {
    return Inertia::render('Landing/LandingWorkWithUs');
})->name('landing.work-with-us');

Route::get('/jobs', function () {
    return Inertia::render('Landing/LandingJobs');
})->name('landing.jobs');

Route::get('/marketing', function () {
    return Inertia::render('Landing/LandingMarketing');
})->name('landing.marketing');

Route::get('/products', function () {
    return Inertia::render('Landing/LandingProducts');
})->name('landing.products');

Route::get('/services', function () {
    return Inertia::render('Landing/LandingServices');
})->name('landing.services');

Route::get('/entrepreneurship', function () {
    return Inertia::render('Landing/LandingEntrepreneurship');
})->name('landing.entrepreneurship');

Route::get('/investments', function () {
    return Inertia::render('Landing/LandingInvestments');
})->name('landing.investments');
