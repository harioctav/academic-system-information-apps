<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  // return view('welcome');
  dd(
    trans('pages-users::page.nav.user.label')
  );
});
