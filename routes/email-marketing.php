<?php

use App\Http\Controllers\Admin\EmailMarketing\CampaignController;
use App\Http\Controllers\Admin\EmailMarketing\InboxController;
use App\Http\Controllers\Admin\EmailMarketing\MailboxSettingsController;
use App\Http\Controllers\Admin\EmailMarketing\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('email-marketing')->name('email.')->group(function () {
    Route::get('inbox', [InboxController::class, 'inbox'])->name('inbox');
    Route::get('sent', [InboxController::class, 'sent'])->name('sent');
    Route::get('drafts', [InboxController::class, 'drafts'])->name('drafts');
    Route::get('starred', [InboxController::class, 'starred'])->name('starred');
    Route::post('inbox/sync', [InboxController::class, 'sync'])->middleware('throttle:10,1')->name('inbox.sync');

    Route::get('compose', [InboxController::class, 'compose'])->name('compose');
    Route::post('send', [InboxController::class, 'send'])->middleware('throttle:30,1')->name('send');
    Route::post('draft', [InboxController::class, 'saveDraft'])->name('draft.save');

    Route::get('message/{emMessage}', [InboxController::class, 'show'])->name('show');
    Route::delete('message/{emMessage}', [InboxController::class, 'destroy'])->name('destroy');
    Route::post('message/{emMessage}/star', [InboxController::class, 'toggleStar'])->name('star');
    Route::post('message/{emMessage}/read', [InboxController::class, 'markRead'])->name('read');
    Route::post('message/{emMessage}/reply', [InboxController::class, 'reply'])->name('reply');
    Route::post('message/{emMessage}/forward', [InboxController::class, 'forward'])->name('forward');
    Route::get('message/{emMessage}/attachments/{attachment}', [InboxController::class, 'downloadAttachment'])->name('attachments.download');

    Route::get('campaigns/preview-recipients', [CampaignController::class, 'previewRecipients'])->name('campaigns.preview-recipients');
    Route::post('campaigns/{emCampaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
    Route::post('campaigns/{emCampaign}/schedule', [CampaignController::class, 'schedule'])->name('campaigns.schedule');
    Route::post('campaigns/{emCampaign}/duplicate', [CampaignController::class, 'duplicate'])->name('campaigns.duplicate');
    Route::resource('campaigns', CampaignController::class)->parameters(['campaigns' => 'emCampaign']);

    Route::post('templates/{emTemplate}/duplicate', [TemplateController::class, 'duplicate'])->name('templates.duplicate');
    Route::get('templates/{emTemplate}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::resource('templates', TemplateController::class)->except(['show'])->parameters(['templates' => 'emTemplate']);

    Route::get('mailbox-settings', [MailboxSettingsController::class, 'edit'])->name('mailbox.settings');
    Route::put('mailbox-settings', [MailboxSettingsController::class, 'update'])->name('mailbox.settings.update');
});
