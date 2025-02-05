<?php

namespace App\Http\Controllers;
use App\Models\Setting;

use Illuminate\Http\Request;

class DownloadAppController extends Controller
{
    public function index()
    {
        $downloadLink = Setting::get('app_download_link', 'https://example.com/default-download');

        return view('app', compact('downloadLink'));
    }
}
