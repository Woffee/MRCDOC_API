<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\Book;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $res = Book::first();
        $uid = $request->only('uid');

        return $this->success($uid);
    }
}
