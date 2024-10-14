<?php

use e282486518\Translatable\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('lang', Controllers\TranslatableController::class.'@index');
