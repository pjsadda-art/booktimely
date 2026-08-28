<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Workdo\Quotation\Http\Controllers\ProposalController;
Route::get('proposal/pdf/{id}', [ProposalController::class, 'proposal'])->name('proposal.pdf');
Route::middleware(['web', 'auth', 'PlanModuleCheck:Purchases'])->group(function () {
   // Proposal
        Route::post('proposal-attechment/{id}', [ProposalController::class, 'proposalAttechment'])->name('proposal.file.upload');
        Route::delete('proposal-attechment/destroy/{id}', [ProposalController::class, 'proposalAttechmentDestroy'])->name('proposal.attachment.destroy');
        Route::post('proposal/customer', [ProposalController::class, 'customer'])->name('proposal.customer');
        Route::post('proposal/product', [ProposalController::class, 'product'])->name('proposal.product');
        Route::get('proposal/{id}/convert', [ProposalController::class, 'convert'])->name('proposal.convert');
        Route::get('proposal/{id}/duplicate', [ProposalController::class, 'duplicate'])->name('proposal.duplicate');
        Route::get('proposal/items', [ProposalController::class, 'items'])->name('proposal.items');
        Route::post('proposal/product/destroy', [ProposalController::class, 'productDestroy'])->name('proposal.product.destroy');
        Route::resource('proposal', ProposalController::class)->except(['create']);
        Route::get('proposal/grid/view', [ProposalController::class, 'Grid'])->name('proposal.grid.view');
        Route::get('proposal/create/{cid}', [ProposalController::class, 'create'])->name('proposal.create');
        Route::get('proposal/{id}/status/change', [ProposalController::class, 'statusChange'])->name('proposal.status.change');
        Route::get('proposal/{id}/resent', [ProposalController::class, 'resent'])->name('proposal.resent');
        Route::post('proposal/section/type', [ProposalController::class, 'ProposalSectionGet'])->name('proposal.section.type');
        Route::get('proposal/{id}/sent', [ProposalController::class, 'sent'])->name('proposal.sent');
        Route::get('proposal/stats/view', [ProposalController::class, 'ProposalQuickStats'])->name('proposal.stats.view');
        Route::get('/proposal/pay/{proposal}', [ProposalController::class, 'payproposal'])->name('pay.proposalpay');


});
