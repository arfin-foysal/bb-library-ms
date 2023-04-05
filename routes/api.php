<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CounteryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\ThirdSubCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/






Route::group(['middleware' => ['auth:sanctum']], function () {

    // Author api
    Route::get('/all-author-list', [AuthorController::class, 'allAuthorList']);
    Route::get('/single-author/{id}', [AuthorController::class, 'singleAuthor']);
    Route::post('/create-or-update-author', [AuthorController::class, 'createOrUpdateAuthor']);
    Route::delete('/delete-author/{id}', [AuthorController::class, 'deleteAuthor']);

    //Catagory api
    Route::get('/all-category-list', [CategoryController::class, 'allCategoryList']);
    Route::get('/single-category/{id}', [CategoryController::class, 'singleCategory']);
    Route::post('/create-or-update-category', [CategoryController::class, 'createOrUpdateCategory']);
    Route::delete('/delete-category/{id}', [CategoryController::class, 'deleteCategory']);

    //SubCatagory api
    Route::get('all-sub-category-list', [SubCategoryController::class, 'allSubCategoryList']);

    Route::get('sub-category-by-category-id/{category_id}', [SubCategoryController::class, 'subCategorybyCategoryId']);
    Route::post('create-or-update-sub-category', [SubCategoryController::class, 'createOrUpdateSubCategory']);
    Route::delete('delete-sub-category/{id}', [SubCategoryController::class, 'deleteSubCategory']);



    //Third Sub Catagory api
    Route::get('all-third-sub-category-list', [ThirdSubCategoryController::class, 'allThirdSubCategoryList']);

    Route::get('third-sub-category-by-sub-category-id/{sub_category_id}', [ThirdSubCategoryController::class, 'ThirdSubCategorybySubCategoryId']);

    Route::post('create-or-update-third-sub-category', [ThirdSubCategoryController::class, 'createOrUpdateThirdSubCategory']);
    
    Route::delete('delete-third-sub-category/{id}', [ThirdSubCategoryController::class, 'deleteThirdSubCategory']);

    //Language api

    Route::get('all-language-list', [LanguageController::class, 'allLanguage']);
    Route::post('create-or-update-language', [LanguageController::class, 'createOrUpdateLanguage']);
    Route::delete('delete-language/{id}', [LanguageController::class, 'deleteLanguage']);

    //Country api
    Route::get('all-countery-list', [CounteryController::class, 'allCounteryList']);
    Route::post('create-or-update-countery', [CounteryController::class, 'createOrUpdateCountery']);
    Route::delete('delete-countery/{id}', [CounteryController::class, 'deleteCountery']);


});









//Authentaction
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);