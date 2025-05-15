<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/dashboard-students-employees', function () {
    return Inertia::render('Dashboard/DashboardStudentsEmployees');
})->name('dashboard.employees');

Route::get('/dashboard-entrepreneurs', function () {
    return Inertia::render('Dashboard/DashboardEntrepreneurs');
})->name('dashboard.entrepreneurs');

Route::get('/dashboard-self-employed', function () {
    return Inertia::render('Dashboard/DashboardSelfEmployed');
})->name('dashboard.selfemployed');

Route::get('/dashboard-study-centers', function () {
    return Inertia::render('Dashboard/DashboardStudyCenter');
})->name('dashboard.studycenters');

Route::get('/dashboard-companies', function () {
    return Inertia::render('Dashboard/DashboardCompanies');
})->name('dashboard.companies');

Route::get('/dashboard-social-entities', function () {
    return Inertia::render('Dashboard/DashboardSocialEntities');
})->name('dashboard.socialentities');

Route::get('/dashboard-online-courses', function () {
    return Inertia::render('Dashboard/DashboardOnlineCourses');
})->name('dashboard.onlinecourses');

Route::get('/dashboard-investors', function () {
    return Inertia::render('Dashboard/DashboardInvestors');
})->name('dashboard.investors');

Route::get('/dashboard-recruiters', function () {
    return Inertia::render('Dashboard/DashboardRecruiters');
})->name('dashboard.recruiters');
