<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/openai', function() {
	$response = Http::withToken(config('services.openai.secret'))
				->post("https://api.openai.com/v1/chat/completions", [
							"model"=> "gpt-3.5-turbo",
							"messages"=> [
								[
									"role"=> "system",
									"content"=> "You are a poetic assistant, skilled in explaining complex programming concepts with creative flair."
								],
								[
									"role"=> "user",
									"content"=> "Compose a poem that explains the concept of recursion in programming."
								],
							]
				])->json();
				dd($response);
});

Route::get('/', function () {
	return view('welcome');
});

Route::get('/dashboard', function () {
	return view('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
	// Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
	// Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
	// Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';

#############################################################################################
// ipma erp general resources controller
require __DIR__.'/General/ajax.php';

#############################################################################################
// ipma erp human resources controller
require __DIR__.'/HumanResources/hr.php';
require __DIR__.'/HumanResources/ajax_hr.php';

#############################################################################################
// ipma erp cps (sales department) controller
require __DIR__.'/Sales/sales.php';
require __DIR__.'/Sales/ajax_sales.php';

#############################################################################################
// ipma erp cps (costing department) controller
require __DIR__.'/Costing/costing.php';
require __DIR__.'/Costing/ajax_costing.php';
























