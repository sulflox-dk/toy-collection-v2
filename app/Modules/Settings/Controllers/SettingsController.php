<?php
namespace App\Modules\Settings\Controllers;

use App\Kernel\Http\Controller;
use App\Kernel\Http\Request;
use App\Kernel\Core\Config;
use App\Modules\Settings\Models\AppSettings;

class SettingsController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('settings_index', [
            'title' => 'Settings',
            'settings' => AppSettings::get(),
            'currencies' => AppSettings::CURRENCIES,
            'saved' => $request->input('saved', '') === '1',
            'baseUrl' => rtrim(Config::get('app.url', ''), '/') . '/',
        ]);
    }

    public function update(Request $request): void
    {
        $currency = strtoupper(trim($request->input('currency', 'USD')));
        if (!in_array($currency, AppSettings::CURRENCIES, true)) {
            $currency = 'USD';
        }

        AppSettings::update(['currency' => $currency]);

        $this->redirect(Request::url('/settings?saved=1'));
    }
}
