<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  // return view('welcome');
  dd(
    __('navigations.regions.group')
  );
});
