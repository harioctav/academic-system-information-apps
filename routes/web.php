<?php

use App\Enums\SubjectNote;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  // return view('welcome');
  dd(SubjectNote::toSelectArray());
});
